<?php
/**
 * Файл
 * Объект ассоциируемый с файлом
 * @version 1.0
 */
namespace Library\basic\File;

use Boolive\data\Entity;
use Boolive\values\Check;
use Boolive\values\Rule;

class File extends Entity
{
    function rule()
    {
        $rule = parent::rule();
        $rule->arrays[0]['file']->arrays[0]['name']->ospatterns($this->validExtentions())->required();
        return $rule;
    }

    /**
     * Шаблоны допустимых имен файлов (расширений)
     * @return array
     */
    function validExtentions()
    {
        return explode(' ', $this->extentions->inner()->value());
    }

    /**
     * Проверка допустимости имени (его расширения)
     * @param $file_name
     * @return bool
     */
    function isValidExtention($file_name)
    {
        Check::ospatterns($file_name, $error, Rule::ospatterns($this->validExtentions()));
        return !isset($error);
    }
}