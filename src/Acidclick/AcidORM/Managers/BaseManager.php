<?php

namespace Acidclick\AcidORM\Managers;

use Nette;

class BaseManager extends Nette\Object{

	protected $data;

	protected function getData(){
		return $this->data;
	}

	protected function setData($data){
		$this->data = $data;
	}

}