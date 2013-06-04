<?php

namespace AcidORM;

use Nette,
	AcidORM\Managers;

class BaseFacade extends Nette\Object{

	protected $name;

	protected $persistorManager;

	protected $mapperManager;

	public function __construct(){
		if(preg_match('/\\\([a-zA-Z]+)Facade$/', $this->getReflection()->name, $regs)){
			$this->name = $regs[1];
		}		
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
			$baseObject->{$propertyName} = 
				$this->persistorManager->getPersistor($oneToMany->className)->getAllForOneToMany(
					$oneToMany,
					$baseObject->id,
					$withDependencies,
					$dependencies
				);
		}

		$properties = $this->mapperManager->getMapper($this->name)->getManyToManyRelationships();
		foreach ($properties as $propertyName => $oneToMany) {
			$baseObject->{$propertyName} = 
				$this->persistorManager->getPersistor($oneToMany->className)->getAllForManyToMany(
					$oneToMany,
					$baseObject->id,
					$withDependencies,
					$dependencies
				);

		}
	}

	public function getPersistor(){
		return $this->persistorManager->getPersistor($this->name);
	}

}