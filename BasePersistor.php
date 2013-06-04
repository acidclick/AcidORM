<?php

namespace AcidORM;

use Nette;

class BasePersistor extends Nette\Object{

	private $db;
	private $object;
	private $mapper;
	private $table;

	private $mapperManager;

	public function __construct($db, $mapperManager){
		$this->db = $db;
		$this->mapperManager = $mapperManager;

		if(preg_match('/\\\([a-zA-Z]+)Persistor$/', $this->getReflection()->name, $regs)){
			$class = 'Model\\Data\\'.$regs[1];
			$this->object = new $class();
			$this->table = $regs[1];
			$this->mapper = $this->mapperManager->getMapper($regs[1]);
		}
	}

	public function getDb(){
		return $this->db;
	}

	public function getMapperManager(){
		return $this->mapperManager();
	}

	public function getMapper(){
		return $this->mapper;
	}

	public function getObject(){
		return $this->object;
	}

	public function insertUpdate(BaseObject $baseObject){
		$array = $this->mapper->toArray($baseObject);
		if($baseObject->id === null){
			$this->db->insert($this->mapper->table, $array)->execute();
			$baseObject->id = $this->db->insertId;
		} else {
			unset($array['id']);
			$this->db->update($this->mapper->table, $array)->where('[id] = %i', $baseObject->id)->execute();
		}
	}

	public function delete($id){
		$this->db->delete($this->mapper->table)->where('id = %i', $id);
	}

	public function getById($id, $withDependencies = false, $dependencies = null){
		if($withDependencies) $dependencies = $this->getDependencies($dependencies);

		$q = $this->createQuery($withDependencies, $dependencies);
		$q = $q->where('[object].[id] = %i', $id);

		if($q->count('*') > 0){
			return $this->map($q->fetch(), $withDependencies, $dependencies);
		}

		return null;
	}

	public function getAll($limit = null, $offset = null, $withDependencies = false, $dependencies = null){
		if($withDependencies) $dependencies = $this->getDependencies($dependencies);
		
		$objects = [];

		$q = $this->createQuery($withDependencies, $dependencies);
		if($limit !== null) $q = $q->limit($limit);
		if($offset !== null) $q = $q->offset($offset);

		if($q->count('*') > 0){
			foreach($q as $r){
				$objects[] = $this->map($r, $withDependencies, $dependencies);
			}

		}

		return $objects;
	}

	public function getByProperty($propertyName, $propertyValue, $withDependencies = false, $dependencies = null){
		if($withDependencies) $dependencies = $this->getDependencies($dependencies);

		$q = $this->createQuery($withDependencies, $dependencies);
		$q = $q->where('[object].['.$propertyName.'] = %s', $propertyValue);

		if($q->count('*') > 0){
			return $this->map($q->fetch(), $withDependencies, $dependencies);
		}

		return null;		
	}

	public function getAllByProperty($propertyName, $propertyValue, $withDependencies = false, $dependencies = null){
		if($withDependencies) $dependencies = $this->getDependencies($dependencies);
		
		$objects = [];

		$q = $this->createQuery($withDependencies, $dependencies)
				  ->where('[object].['.$propertyName.'] = %s', $propertyValue);

		if($q->count('*') > 0){
			foreach($q as $r){
				$objects[] = $this->map($r, $withDependencies, $dependencies);
			}

		}

		return $objects;
	}

	public function getAllForOneToMany(DB\Relationships\OneToMany $oneToMany, $value, $withDependencies = false, $dependencies = null){
		return $this->getAllByProperty($oneToMany->propertyName, $value, $withDependencies, $dependencies);
	}

	public function getAllForManyToMany(DB\Relationships\ManyToMany $manyToMany, $value, $withDependencies = false, $dependencies = null){
		if($withDependencies) $dependencies = $this->getDependencies($dependencies);

		$objects = [];

		$q = $this->createQuery($withDependencies, $dependencies)
				  ->join('['.$table.'] [rel1]')->on(sprintf('[object].[id] = [rel1].[%s]', $manyToMany->foreignKey))
				  ->where(sprintf('[rel1].[%s] = %%s', $manyToMany->column), $manyToMany->value);

		if($q->count('*') > 0){
			foreach($q as $r){
				$objects[] = $this->map($r, $withDependencies, $dependencies);
			}

		}

		return $objects;
	}	

	public function map($r, $withDependencies = false, $dependencies = null){
		$result = new DB\Result($r);
		$object = $this->mapper->map($result->getAliasData('object'));
		if($withDependencies){
			foreach($dependencies as $property => $dependency){
				$dependencyObject = $this->mapperManager->getMapper($dependency->className)->map($result->getAliasData($property));
				$object->$property = $dependencyObject;
			}
		}
		return $object;		
	}

	public function getDependencies($dependencies = null){
		if($dependencies === null){
			$newDependencies = [];
			foreach($this->mapper->getOneToOneRelationships() as $property => $hasOne){
				$newDependencies[] = $property;
			}
			$dependencies = $newDependencies;
		}

		$data  = [];
		foreach ($dependencies as $propertyName) {
			$property = $this->object->getReflection()->getProperty($propertyName);
			if($property->hasAnnotation('oneToOne')){
				$annotation = $property->getAnnotation('oneToOne');
				$data[$propertyName] = new DB\Relationships\OneToOne($annotation['className'], $annotation['propertyName']);
			}
		}
		return $data;
	}

	public function createQuery($withDependencies = false, $dependencies = null){
		$columns = $this->mapper->getColumns('object');
		if($withDependencies){
			foreach($dependencies as $propertyName => $dependency){
				$columns = array_merge($columns, $this->mapperManager->getMapper($dependency->className)->getColumns($propertyName));
			}
		}
		$q = $this->db->select($columns)
					  ->from(sprintf('[%s] [%s]', $this->mapper->getTable(), 'object'));
		if($withDependencies){
			foreach($dependencies as $property => $dependency){
				$table = $this->mapperManager->getMapper($dependency->className)->getTable();
				$alias = $property;
				$q = $q->join(sprintf('[%s] [%s]', $table, $alias))
					   ->on(sprintf('[%s].[%s] = [%s].[%s]', $alias, 'id', 'object', $dependency->propertyName));
			}
		}
		return $q;
	}
}