<?php

namespace Acidclick\AcidORM\Generators;

interface IDatabaseFirst
{
	function createFromTable($table);

	function createAll();
}