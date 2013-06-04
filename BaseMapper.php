<?php

namespace AcidORM;

use Nette,
	AcidORM\DB;

class BaseMapper extends Nette\Object{

	private $namespace = 'Model\\Data\\';

	private $object;

	private $table;
	private $oneToOneRelations;
	private $manyToManyRelations;
	private $oneToManyRelations;

	public function __construct(){
		if(preg_match('/([a-zA-Z]+)Mapper$/', $this->getReflection()->name, $regs)){
			$className = $this->namespace . $regs[1];
			$this->object = new $className();
			$this->table = $regs[1];
		}
	}

	public function toArray(BaseObject $object){
		$reflection = $this->object->getReflection();
		$array = [];
		foreach($reflection->getProperties() as $property){
			if(!$property->hasAnnotation('oneToOne') && !$property->hasAnnotation('oneToMany') && !$property->hasAnnotation('manyToMany')){
				if($object->{$property->name} !== null) $array[$property->name] = $object->{$property->name};	
			}
			
		}
		return $array;
	}

	public function map($data){
		$object = clone $this->object;
		$reflection = $object->getReflection();
		foreach($data as $property => $value){
			if($reflection->hasProperty($property)){
				$object->{$property} = $value;
			}
		}
		return $object;
	}

	public function create(){
		return clone $this->object;
	}

	public function getColumns($alias){
		$columns = [];
		foreach($this->object->getReflection()->getProperties() as $property){
			if(!$property->hasAnnotation('oneToOne') && !$property->hasAnnotation('manyToMany') && !$property->hasAnnotation('oneToMany')){
				$columns[$alias . '.' . $property->name] =  $alias . '_' . $property->name;
			}
		}		
		return $columns;
	}

	public function getManyToManyRelationships(){
		if($this->manyToManyRelations === null){
			$this->manyToManyRelations = [];
			foreach($this->object->getReflection()->getProperties() as $property){
				if($property->hasAnnotation('manyToMany')){
					$annotation = $property->getAnnotation('manyToMany');
					$manyToMany = new DB\Relationships\ManyToMany(
						$annotation['className'],
						$annotation['table'],
						$annotation['foreignKey'],
						$annotation['column']
					);
					$this->manyToManyRelations[$property->name] = $manyToMany;
				}
			}
		}
		return $this->manyToManyRelations;
	}	

	public function getOneToOneRelationships(){
		if($this->oneToOneRelations === null){
			$this->oneToOneRelations = [];
			foreach($this->object->getReflection()->getProperties() as $property){
				if($property->hasAnnotation('oneToOne')){
					$annotation = $property->getAnnotation('oneToOne');
					$oneToOne = new DB\Relationships\OneToOne(
						$annotation['className'],
						$annotation['propertyName']
					);
					$this->oneToOneRelations[$property->name] = $oneToOne;
				}
			}
		}
		return $this->oneToOneRelations;
	}	

	public function getOneToManyRelationships(){
		if($this->oneToManyRelations === null){
			$this->oneToManyRelations = [];
			foreach($this->object->getReflection()->getProperties() as $property){
				if($property->hasAnnotation('oneToMany')){
					$annotation = $property->getAnnotation('oneToMany');
					$oneToMany = new DB\Relationships\OneToMany(
						$annotation['className'],
						$annotation['foreignKey']
					);
					$this->oneToManyRelations[$property->name] = $oneToMany;
				}
			}
		}
		return $this->oneToManyRelations;
	}	

	public function getTable(){
		return $this->table;
	}	

}