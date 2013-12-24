<?php

namespace AcidORM\Managers;

use Nette;

class FacadeManager extends BaseManager{

	protected $namespace = 'Model\\Facades\\';

	protected $persistorManager;

	protected $mapperManager;

	protected $cache;

	protected $parameters;

	public function getFacade($name)
	{
		$className = $this->namespace . $name . 'Facade';
		if(!isset($this->data[$className])){
			$facade = $this->data[$className] = new $className;
			$facade->persistorManager = $this->persistorManager;
			$facade->mapperManager = $this->mapperManager;
			$facade->cache = $this->cache;
			$facade->facadeManager = $this;
			$facade->parameters = $this->parameters;
			$facade->startup();
		}
		return $this->data[$className];
	}

	public function setPersistorManager(PersistorManager $persistorManager)
	{
		$this->persistorManager = $persistorManager;
	}

	public function getPersistorManager()
	{
		return $this->persistorManager;
	}

	public function getMapperManager()
	{
		return $this->mapperManager;
	}

	public function setMapperManager(MapperManager $mapperManager)
	{
		$this->mapperManager = $mapperManager;
	}

	public function setCache(Nette\Caching\Cache $cache)
	{
		$this->cache = $cache;
	}

	public function getCache()
	{
		return $this->cache;
	}

	public function setParameters($parameters)
	{
		$this->parameters = $parameters;
	}

	public function getParameters()
	{
		return $this->parameters;
	}

    public function &__get($name)
    {
    	if(preg_match('/^(.+)Facade$/', $name, $regs)){
    		$facade = $this->getFacade(Nette\Utils\Strings::firstUpper($regs[1]));
    		return $facade;
    	}

    	parent::__get($name);
    }	

}