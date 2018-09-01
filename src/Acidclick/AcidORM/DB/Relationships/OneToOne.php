<?php

namespace Acidclick\AcidORM\DB\Relationships;

use Nette;

class OneToOne extends Nette\Object{

	private $className;
	private $propertyName;
	private $canBeNull;

	public function __construct($className, $propertyName, $canBeNull = false){
		$this->className = $className;
		$this->propertyName = $propertyName;
		$this->canBeNull = $canBeNull;
	}

	public function getClassName(){
		return $this->className;
	}

	public function getPropertyName(){
		return $this->propertyName;
	}
	
	public function getCanBeNull(){
		return $this->canBeNull;
	}
}