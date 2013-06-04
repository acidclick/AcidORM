<?php

namespace AcidORM\Managers;

use Nette;

class FacadeManager extends BaseManager{

	protected $namespace = 'Model\\Facades\\';

	protected $persistorManager;

	protected $mapperManager;

	public function getFacade($name){
		$className = $this->namespace . $name . 'Facade';
		if(!isset($this->data[$className])){
			$facade = $this->data[$className] = new $className;
			$facade->persistorManager = $this->persistorManager;
			$facade->mapperManager = $this->mapperManager;
		}
		return $this->data[$className];
	}

	public function setPersistorManager(PersistorManager $persistorManager){
		$this->persistorManager = $persistorManager;
	}

	public function getPersistorManager(){
		return $this->persistorManager;
	}

	public function getMapperManager(){
		return $this->mapperManager;
	}

	public function setMapperManager(MapperManager $mapperManager){
		$this->mapperManager = $mapperManager;
	}

}