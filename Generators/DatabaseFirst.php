<?php

namespace AcidORM\Generators;

use Nette;

class DatabaseFirst extends Nette\Object implements IDatabaseFirst{

	private $db;
	private $databaseDriver;
	private $adapter;
	private $appDir;

	public function getDb()
	{
		return $this->db;
	}

	public function setDb($db)
	{
		$this->db = $db;
	}

	public function getDatabaseDriver()
	{
		return $this->databaseDriver;
	}

	public function setDatabaseDriver($databaseDriver)
	{
		$this->databaseDriver = $databaseDriver;
	}

	public function getAppDir()
	{
		return $this->appDir;
	}	

	public function setAppDir($appDir)
	{
		$this->appDir = $appDir;
	}	

	public function createAdapter()
	{
		$class = 'AcidORM\\Generators\\DB\\' . Nette\Utils\Strings::firstUpper($this->databaseDriver);
		$this->adapter = new $class();
		$this->adapter->db = $this->db;
		$this->adapter->appDir = $this->appDir;
	}

	public function createFromTable($table)
	{
		$this->adapter || $this->createAdapter();
		$this->adapter->createFromTable($table);
	}

	public function createAll()
	{
		$this->adapter || $this->createAdapter();
		$this->adapter->createAll();
	}




}