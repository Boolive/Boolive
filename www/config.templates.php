<?php
/**
 * Конфигурация шаблонизаторов
 * Указывается маска расширения и соответсвующей ей класс шаблонизатора
 */
$config = array(
    '*.tpl' => '\Boolive\template\php\PHPTemplate',
    '*.txt' => '\Boolive\template\text\TextTemplate',
);