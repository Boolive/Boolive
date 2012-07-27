<?php
/**
 * Конфигурация шаблонизаторов
 * Указывается маска расширения и соответсвующей ей класс шаблонизатора
 */
$config = array(
	'*.tpl' => '\Engine\Template\PHPTemplate',
	'*.txt' => '\Engine\Template\TextTemplate',
);