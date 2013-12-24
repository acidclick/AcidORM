<?php

namespace AcidORM\Generators\DB;

use Nette,
	AcidORM\Generators;

class Postgre extends Nette\Object implements Generators\IDatabaseFirst{

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
			//exit;

			$this->createData($rt->tablename, $properties, $dependencies);
			$this->createPersistor($rt->tablename);
			$this->createMapper($rt->tablename);
			$this->createFacade($rt->tablename);
		}
		    
	}

	public function createData($name, $properties, $dependencies)
	{
		$userDefinedProperties = '';
		$userDefinedMethods = '';

		$filepath = sprintf('%s/model/Data/%s.php', $this->appDir, $name);
		if(file_exists($filepath)){
			$fp = fopen($filepath, 'r');
			$content = fread($fp, filesize($filepath));
			fclose($fp);

			$userDefinedPropertiesStart = false;
			$userDefinedMethodsStart = false;

			foreach(preg_split('/\n/', $content) as $line){
				if(preg_match('/\/\/\sUser\sdefined\sproperties/', $line)){
					$userDefinedPropertiesStart = !$userDefinedPropertiesStart;
					continue;
				}

				if(preg_match('/\/\/\sUser\sdefined\smethods/', $line)){
					$userDefinedMethodsStart = !$userDefinedMethodsStart;
					continue;
				}

				if($userDefinedPropertiesStart) $userDefinedProperties .= $line . "\n";
				if($userDefinedMethodsStart) $userDefinedMethods .= $line . "\n";
			}
		}

		$data  = sprintf("<?php\n\nnamespace Model\Data;\n\nuse Nette,\n\tAcidORM;\n\nclass %s extends AcidORM\BaseObject\n{\n\n", $name);

		$data .= "\t// AcidORM generated properties\n\n";

		foreach($properties as $property){
			$data .= sprintf("\tprivate \$%s;\n", $property->name);
		}

		$data .= "\n";

		foreach($dependencies as $dependency){
			$data .= sprintf("\t/** %s */\n", $dependency->annotation);
			$data .= sprintf("\tprivate \$%s;\n", $dependency->name);
		}

		$data .= "\n\t// AcidORM generated properties";

		$data .= "\n\n\t// User defined properties\n";

		$data .= $userDefinedProperties;

		$data .= "\t// User defined properties";

		$data .= "\n\n\t// AcidORM generated methods\n\n";
		
		foreach($properties as $property){
			$data .= sprintf("\tpublic function get%s()\n\t{\n\t\treturn \$this->%s;\n\t}\n\n", Nette\Utils\Strings::firstUpper($property->name), $property->name);
			$data .= sprintf("\tpublic function set%s(\$%s)\n\t{\n\t\t\$this->%s = \$%s;\n\t}\n\n", Nette\Utils\Strings::firstUpper($property->name), $property->name, $property->name, $property->name);
		}

		foreach($dependencies as $dependency){
			$data .= sprintf("\tpublic function get%s()\n\t{\n\t\treturn \$this->%s;\n\t}\n\n", Nette\Utils\Strings::firstUpper($dependency->name), $dependency->name);
			$data .= sprintf("\tpublic function set%s(\$%s)\n\t{\n\t\t\$this->%s = \$%s;\n\t}\n\n", Nette\Utils\Strings::firstUpper($dependency->name), $dependency->name, $dependency->name, $property->name);
		}		

		$data .= "\t// AcidORM generated methods\n\n";

		$data .= "\t// User defined methods\n";

		$data .= $userDefinedMethods;

		$data .= "\t// User defined methods\n\n";

		$data .= "}";

		$this->saveFile($data, sprintf('Data/%s.php', Nette\Utils\Strings::firstUpper($name)));

	}

	public function createPersistor($name)
	{
		$data  = sprintf("<?php\n\nnamespace Model\Persistors;\n\nuse Nette,\n\tAcidORM;\n\nclass %sPersistor extends AcidORM\BasePersistor\n{\n\n}", $name);
		$this->saveFile($data, sprintf('Persistors/%sPersistors.php', Nette\Utils\Strings::firstUpper($name)));
	}

	public function createMapper($name)
	{
		$data  = sprintf("<?php\n\nnamespace Model\Mappers;\n\nuse Nette,\n\tAcidORM;\n\nclass %sMapper extends AcidORM\BaseMapper\n{\n\n}", $name);
		$this->saveFile($data, sprintf('Mappers/%sMapper.php', Nette\Utils\Strings::firstUpper($name)));
	}

	public function createFacade($name)
	{
		$data  = sprintf("<?php\n\nnamespace Model\Facades;\n\nuse Nette,\n\tAcidORM;\n\nclass %sFacade extends AcidORM\BaseFacade\n{\n\n}", $name);
		$this->saveFile($data, sprintf('Facades/%sFacade.php', Nette\Utils\Strings::firstUpper($name)));
	}	

	private function saveFile($data, $filepath)
	{
		$fp = fopen($this->appDir . '/model/' . $filepath, 'w');
		fwrite($fp, $data);
		fclose($fp);
	}
}