<?php

namespace Acidclick\AcidORM\Managers;

use Nette;

class GridManager extends BaseManager{

	protected $namespace = 'Model\\Grids\\';

	protected $db;

	public function getGrid($name){
		$className = $this->namespace . $name . 'Grid';
		if(!isset($this->data[$className])){
			$grid = $this->data[$className] = new $className();
			$grid->db = $this->db;
		}
		return $this->data[$className];
	}

	public function setDb(\Dibi\Connection $db){
		$this->db = $db;
	}

    public function &__get($name)
    {
    	if(preg_match('/^(.+)Grid$/', $name, $regs)){
    		$grid = $this->getGrid(Nette\Utils\Strings::firstUpper($regs[1]));
    		return $grid;
    	}

    	parent::__get($name);
    }

}