<?php

namespace AcidORM\Managers;

use Nette;

class MapperManager extends BaseManager{

	protected $namespace = 'Model\\Mappers\\';

	public function getMapper($name){
		$className = $this->namespace . $name . 'Mapper';
		if(!isset($this->data[$className])){
			$this->data[$className] = new $className();
		}
		return $this->data[$className];
	}
}