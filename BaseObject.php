<?php

namespace AcidORM;

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

}