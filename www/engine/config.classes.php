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
	'Engine\DebugPDOStatement'	=> 'database/DebugPDOStatement.php',
);
