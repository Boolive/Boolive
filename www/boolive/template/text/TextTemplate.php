<?php
/**
 * Шаблонизация простой заменой {*} в тексте на значения
 *
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace boolive\template\text;

use boolive\data\Entity;

class TextTemplate
{
    /**
     * Создание текста из шаблона
     * В шаблон вставляются переданные значения
     * При обработки шаблона могут довыбираться значения из $entity и создаваться команды в $commands
     * @param \boolive\data\Entity $entity
     * @param array $v
     * @throws \Exception
     * @return string
     */
    function render($entity, $v)
    {
        $text = file_get_contents($entity->file(null, true));
        $vars = filter_var_array($v, FILTER_SANITIZE_SPECIAL_CHARS);
        // По циклу проходимся по всем переменным заменяя значения в {} на значения в массиве
        if (is_array($vars)){
            foreach ($vars as $key => $value){
                $text = str_replace('{'.$key.'}', $value, $text);
            }
        }
        return $text;
    }
}