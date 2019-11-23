<?php

namespace AcidORM\Managers;

use Nette;
/**
 * @property MapperManager $mapperManager
 * @property string $namespace
 * @property \DibiConnection $db
 * @property Nette\Caching\Cache $cache
 */
class PersistorManager extends BaseManager
{

	protected $namespace = 'Model\\Persistors\\';

	protected $mapperManager;

	protected $db;

	protected $cache;

	public function getPersistor($name){
		$className = $this->namespace . $name . 'Persistor';
		if(!isset($this->data[$className])){
			$this->data[$className] = $persistor = new $className($this->db, $this->mapperManager);
			$persistor->cache = $this->cache;
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

	public function setCache(Nette\Caching\Cache $cache)
	{
		$this->cache = $cache;
	}

	public function getCache()
	{
		return $this->cache;
	}	

    public function &__get($name)
    {
    	if(preg_match('/^(.+)Persistor$/', $name, $regs)){
    		$persistor = $this->getPersistor(Nette\Utils\Strings::firstUpper($regs[1]));
    		return $persistor;
    	}

    	parent::__get($name);
    }	
}