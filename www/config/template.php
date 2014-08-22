<?php
/**
 * Конфигурация шаблонизаторов
 * Указывается маска расширения и соответсвующей ей класс шаблонизатора
 * @var array
 */
return array(
    '*.tpl' => '\boolive\template\php\PHPTemplate',
    '*.txt' => '\boolive\template\text\TextTemplate',
    '*' => '\boolive\template\php\PHPTemplate',
);