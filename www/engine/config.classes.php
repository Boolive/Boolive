<?php
/**
 * Классы движка (ядра системы)
 */
$classes = array(
	// Ядро
	'Engine\Engine'			=> 'Engine.php',

	// Управление классами
	'Engine\Classes'		=> 'classes/Classes.php',

	// Отладка и сбор статистики по движку
	'Engine\Trace'			=> 'develop/Trace.php',
	'Engine\ITrace'			=> 'develop/Trace.php',
	'Engine\Benchmark'		=> 'develop/Benchmark.php',

	// Обработка ошибок
	'Engine\ErrorsHandler'	=> 'errors/ErrorsHandler.php',
	'Engine\Error'			=> 'errors/Error.php',
	
	// События
	'Engine\Events'			=> 'events/Events.php',
	'Engine\EventResult'	=> 'events/EventResult.php',

	// Поддержка Юникода (UTF-8)
	'Engine\Unicode'		=> 'unicode/Unicode.php',

	// База данных
	'Engine\DB'					=> 'database/DB.php',
	'Engine\DebugDBStatement'	=> 'database/DebugDBStatement.php',

	// Отложенные вызовы
	'Engine\Calls'			=> 'calls/Calls.php',

	// Значения, правила проверки, проверка и фильтр
	'Engine\Rule'			=> 'values/Rule.php',
	'Engine\Check'			=> 'values/Check.php',
	'Engine\Values'			=> 'values/Values.php',

	// Модуль данных, базовые классы объектов данных
	'Engine\Data'			=> 'data/Data.php',
	'Engine\Entity'			=> 'data/Entity.php',
	'Engine\Root'			=> 'data/Root.php',
	'Engine\Section'		=> 'data/Section.php',
	// Секции
	'Engine\Sections\MySQLSection'	=> 'data/sections/MySQLSection.php',

	// Общие функции
	'Engine\F'			=> 'functions/F.php',

	// Работа с файлами
	'Engine\File'		=> 'file/File.php'
);
