<?php

namespace AcidORM\DB\Relationships;

use Nette;

class OneToOne extends Nette\Object{

	private $className;
	private $propertyName;

	public function __construct($className, $propertyName){
		$this->className = $className;
		$this->propertyName = $propertyName;
	}

	public function getClassName(){
		return $this->className;
	}

	public function getPropertyName(){
		return $this->propertyName;
	}
	
}