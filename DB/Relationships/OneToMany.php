<?php

namespace AcidORM\DB\Relationships;

use Nette;

class OneToMany extends Nette\Object{

	private $className;
	private $foreignKey;

	public function __construct($className, $foreignKey){
		$this->className = $className;
		$this->foreignKey = $foreignKey;
	}

	public function getClassName(){
		return $this->className;
	}

	public function getPropertyName(){
		return $this->foreignKey;
	}
	
}