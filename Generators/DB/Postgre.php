<?php

namespace AcidORM\Generators\DB;

use Nette,
	AcidORM\Generators;

class Postgre extends BaseDatabase implements Generators\IDatabaseFirst{

	private $db;
	private $appDir;

	public function getDb()
	{
		return $this->db;
	}

	public function setDb($db)
	{
		$this->db = $db;
	}

	public function getAppDir()
	{
		return $this->appDir;
	}

	public function setAppDir($appDir)
	{
		$this->appDir = $appDir;
	}

	public function createFromTable($table)
	{
		$this->db->query('\d+ ' . $table);
	}

	public function createAll()
	{
		$qt = $this->db->query('SELECT * FROM [pg_catalog].[pg_tables] where [schemaname] = %s', 'public');
		foreach($qt as $rt){
			$qc = $this->db->query('select * from [information_schema].[columns] where [table_name] = %s', $rt->tablename);

			$properties = [];
			foreach($qc as $qr){
				$properties[] = (Object)['name' => $qr->column_name];
			}
			$properties = array_reverse($properties);

			$qfk = $this->db->query('
				SELECT obj_description(%sql::regclass) as note
				FROM pg_class
				WHERE relkind = %s limit 1
			', '\'"' . $rt->tablename . '"\'', 'r');

			$dependencies = [];
			foreach(preg_split('/\n/', $qfk->fetch()->note) as $line){
				if(preg_match('/^([^:]+):((@oneToOne|@oneToMany|@manyToMany)[^$]+)$/', $line, $regs)){
					$dependencies[] = (Object)[
						'name' => $regs[1],
						'annotation' => $regs[2]
					];
				}
			}

			$this->createData($rt->tablename, $properties, $dependencies);
			$this->createPersistor($rt->tablename);
			$this->createMapper($rt->tablename);
			$this->createFacade($rt->tablename);
		}
		    
	}

}