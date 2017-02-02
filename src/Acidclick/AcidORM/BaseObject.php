<?php

namespace Acidclick\AcidORM;

use Nette;

class BaseObject extends Nette\Object implements \JsonSerializable{

	public function jsonSerialize()
	{
		$data = [];
		foreach($this->getReflection()->getProperties() as $property){
			$data[$property->name] = $this->{$property->name};
		}
		return $data;
	}

	public function getLabel($name = null)
	{
		if(property_exists($this, 'label')){
			return $this->label;
		} else if($name !== null){
			if(property_exists($this, $name)){
				if($this->reflection->getProperty($name)->hasAnnotation('label')){
					return $this->reflection->getProperty($name)->getAnnotation('label');
				}
			}
		}

		return '@' . $name;
	}

}