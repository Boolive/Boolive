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
    public function defineRule()
    {
        parent::defineRule();
        $this->_rule->arrays[0]['file']->arrays[0]['name']->ospatterns($this->validExtentions())->required();
    }

    /**
     * Шаблоны допустимых имен файлов (расширений)
     * @return array
     */
    public function validExtentions()
    {
        return explode(' ', $this->extentions->value());
    }

    /**
     * Проверка допустимости имени (его расширения)
     * @param $file_name
     * @return bool
     */
    public function isValidExtention($file_name)
    {
        Check::ospatterns($file_name, $error, Rule::ospatterns($this->validExtentions()));
        return !isset($error);
    }
}