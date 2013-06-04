<?php

namespace AcidORM\Managers;

use Nette;

class GridManager extends BaseManager{

	protected $namespace = 'Model\\Grids\\';

	public function getGrid($name){
		$className = $this->namespace . $name . 'Grid';
		if(!isset($this->data[$className])){
			$this->data[$className] = new $className();
		}
		return $this->data[$className];
	}
}