<?php

namespace AcidORM;

use Nette,
	AcidORM\Managers,
	AcidORM\Interfaces\IHistoryProxy;

class BaseFacade extends Nette\Object{

	protected $name;

	protected $persistorManager;

	protected $mapperManager;

	private $cache;

	protected $facadeManager;

	protected $parameters;

	public function __construct(){
		if(preg_match('/\\\([a-zA-Z]+)Facade$/', $this->getReflection()->name, $regs)){
			$this->name = $regs[1];
		}	
	}

	public function startup()
	{

	}

	public function setPersistorManager(Managers\PersistorManager $persistorManager){
		$this->persistorManager = $persistorManager;
	}

	public function setMapperManager(Managers\MapperManager $mapperManager){
		$this->mapperManager = $mapperManager;
	}	

	public function mapDependencies(BaseObject &$baseObject = null, $withDependencies = false, $dependencies = null){
		if($baseObject === null) return;
		$properties = $this->mapperManager->getMapper($this->name)->getOneToManyRelationships();
		foreach($properties as $propertyName => $oneToMany){
			if($dependencies === null || in_array($propertyName, $dependencies))
				$baseObject->{$propertyName} = 
					$this->persistorManager->getPersistor($oneToMany->className)->getAllForOneToMany(
						$oneToMany,
						$baseObject->id,
						true
					);
		}

		$properties = $this->mapperManager->getMapper($this->name)->getManyToManyRelationships();
		foreach ($properties as $propertyName => $oneToMany) {
			if($dependencies === null || in_array($propertyName, $dependencies))
				$baseObject->{$propertyName} = 
					$this->persistorManager->getPersistor($oneToMany->className)->getAllForManyToMany(
						$oneToMany,
						$baseObject->id,
						true
					);

		}
	}

	public function getPersistor(){
		return $this->persistorManager->getPersistor($this->name);
	}

	public function setCache(Nette\Caching\Cache $cache)
	{
		$this->cache = $cache;
	}

	public function getCache()
	{
		return $this->cache;
	}

	public function getKeyValuePairs($className = null, $key = 'id', $value = 'name')
	{
		if($className === null) $className = $this->name;
		$keyValuePairs = $this->persistorManager->getPersistor($className)->getKeyValuePairs($key, $value);
		return $keyValuePairs;
	}

	public function setFacadeManager(Managers\FacadeManager &$facadeManager)
	{
		$this->facadeManager = $facadeManager;
	}

	public function setParameters($parameters)
	{
		$this->parameters = $parameters;
	}

	public function &__call($name, $args)
	{

		if(method_exists($this, $name)){
			$result = call_user_func_array([$this, $name], $args);
			return $result;
		}

		if(preg_match('/^get([A-Z]{1}.+)By([A-Z]{1}.+)$/', $name, $regs)){
			if($this->isCallable($regs[1])){
				if($this->isPlural($regs[1])){
					$result = $this->simpleGetAllBy($this->getSingular($regs[1]), $regs[2], $args);
					return $result;
				} else {
					$result = $this->simpleGetBy($regs[1], $regs[2], $args);
					return $result;
				}
			}

		}

		if(preg_match('/^get([A-Z]{1}.+)$/', $name, $regs)){
			$result = $this->simpleGetAll($this->getSingular($regs[1]), $args);
			return $result;
		}

		if(preg_match('/^delete([A-Z]{1}.+)$/', $name, $regs)){
			$result = $this->simpleDelete($regs[1], $args[0]);
			return $result;
		}

		if(preg_match('/^insertUpdate([A-Z]{1}.+)$/', $name, $regs)){
			$result = $this->simpleInsertUpdate($regs[1], $args[0], isset($args[1]) ? $args[1] : null);
			return $result;
		}

		parent::__call($name, $args);
	}

	private function isCallable($class)
	{
		
		if($class !== $this->name && !$this->isPlural($class)){
			throw new \Exception(sprintf('Object %s cannot be called from %sFacade.', $class, $this->name));
		}		

		return true;
	}

	private function isPlural($class)
	{
		if($class === $this->name) return false;

		$reflection = new Nette\Reflection\ClassType('Model\\Data\\' . $this->name);
		$plural = $reflection->getAnnotation('plural');

		return $plural === $class;
	}

	private function getSingular($class)
	{
		return $this->name;
	}

	private function simpleGetBy($class, $calledProperties, $values)
	{
		foreach(preg_split('/And/', $calledProperties) as $index => $property){
			$property = Nette\Utils\Strings::lower(Nette\Utils\Strings::substring($property, 0, 1)) . Nette\Utils\Strings::substring($property, 1);
			if(!property_exists('Model\\Data\\' . $class, $property)){
				throw new \Exception(sprintf('Object %s has no property called %s.', $class, $property));
			}

			if(!isset($values[$index])){
				throw new \Exception(sprintf('Missing %s param.', $property));
			}

			$params[$property] = $values[$index];
		}

		$object = $this->persistor->getByProperties($params, true);
		if($object !== null) $this->mapDependencies($object);
		return $object;
	}

	private function simpleGetAllBy($class, $calledProperties, $values)
	{
		foreach(preg_split('/And/', $calledProperties) as $index => $property){
			$property = Nette\Utils\Strings::lower(Nette\Utils\Strings::substring($property, 0, 1)) . Nette\Utils\Strings::substring($property, 1);
			if(!property_exists('Model\\Data\\' . $class, $property)){
				throw new \Exception(sprintf('Object %s has no property called %s.', $class, $property));
			}

			if(!isset($values[$index])){
				throw new \Exception(sprintf('Missing %s param.', $property));
			}

			$params[$property] = $values[$index];
		}

		$count = 0;
		$objects = $this->persistor->getAllByProperties(
			$params, 
			true, 
			null,   
			sizeof($values) >= sizeof($params) + 1 ? $values[sizeof($params)] : null, 
			sizeof($values) >= sizeof($params) + 2 ? $values[sizeof($params) + 1] : null,
			$count
		);
		foreach($objects as $object){
			if($object !== null) $this->mapDependencies($object);
		}
		return $objects;
	}	

	private function simpleGetAll($class, $values)
	{

		$objects = $this->persistor->getAll(isset($values[0]) ? $values[0] : null, isset($values[1]) ? $values[1] : null, true);
		foreach($objects as $object){
			if($object !== null) $this->mapDependencies($object);
		}
		return $objects;
	}	

	private function simpleDelete($class, $id)
	{
		$this->isCallable($class);

		$this->persistor->delete($id);
	}

	private function simpleInsertUpdate($class, $object, $userId = null)
	{
		$this->isCallable($class);

		$new = $object->id === null;
		if(!$new && $this instanceof IHistoryProxy){
			$oldObject = $this->simpleGetBy($class, 'Id', [$object->id]);
		}

		$this->persistor->insertUpdate($object);

		if($this instanceof IHistoryProxy)
		{
			if($new){
				$namespacedClass = sprintf('Model\\Data\\%s', $class);
				$oldObject = new $namespacedClass();
			}
			$newObject = $this->simpleGetBy($class, 'Id', [$object->id]);
			foreach($newObject->getReflection()->getProperties() as $property){
				if($property->hasAnnotation('label') && $property->hasAnnotation('historyDontMap')) $oldObject->{$property->name} = $object->{$property->name};
			}
			if(Utils\HistoryComparer::hasChanges($oldObject, $newObject)){
				$history = new \Model\Data\History;
				$objectKey = Nette\Utils\Strings::lower(Nette\Utils\Strings::substring($this->name, 0, 1)) . Nette\Utils\Strings::substring($this->name, 1) . 'Id';

				$history->$objectKey = $object->id;
				$history->userId = $userId;
				$history->created = date('Y-m-d H:i:s');
				$history->changes = Utils\HistoryComparer::getChanges($oldObject, $newObject);
				$this->facadeManager->historyFacade->insertUpdateHistory($history);
			}			
		}
	}	

	public function cleanCacheByTag($tag)
	{
		$this->cache->clean([Nette\Caching\Cache::TAGS => [$tag]]);
	}

	public function getKeyValuePairsHierarchy(){
		$data = [];
		$this->getPersistor()->getKeyValuePairsHierarchy($data);
		return $data;
	}	

}