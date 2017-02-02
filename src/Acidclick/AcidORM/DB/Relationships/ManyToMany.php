<?php

namespace Acidclick\AcidORM\DB\Relationships;

use Nette;

class ManyToMany extends Nette\Object
{

	private $table;
	private $foreignKey;
	private $column;
	private $className;

	public function __construct($className, $table, $foreignKey, $column)
	{
		$this->className = $className;
		$this->table = $table;
		$this->foreignKey = $foreignKey;
		$this->column = $column;
	}

	public function getTable()
	{
		return $this->table;
	}

	public function getForeignKey()
	{
		return $this->foreignKey;
	}

	public function getColumn()
	{
		return $this->column;
	}

	public function getClassName()
	{
		return $this->className;
	}

}
