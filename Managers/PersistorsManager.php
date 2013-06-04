<?php

namespace AcidORM\Managers;

use Nette;

class PersistorManager extends BaseManager{

	protected $namespace = 'Model\\Persistors\\';

	protected $mapperManager;

	protected $db;

	public function getPersistor($name){
		$className = $this->namespace . $name . 'Persistor';
		if(!isset($this->data[$className])){
			$this->data[$className] = new $className($this->db, $this->mapperManager);
		}
		return $this->data[$className];
	}

	public function setMapperManager(MapperManager $mapperManager){
		$this->mapperManager = $mapperManager;
	}

	public function getMapperManager(){
		return $this->mapperManage;
	}

	public function setDb($db){
		$this->db = $db;
	}
}