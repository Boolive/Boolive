<?php
/**
 * Шаблонизатор
 * "Мост" к конкретным шаблонизаторам. Выбор происходит автоматически по расширениям файлов-шаблонов
 * @link http://boolive.ru/createcms/making-page
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace boolive\template;

use boolive\config\Config;
use boolive\errors\Error,
    boolive\functions\F;

class Template
{
    /** @var array Массив названий классов шаблонизаторов */
    static private $engines;

    /**
     * Загрузка шаблонизаторов
     */
    static function activate(){
        self::$engines = Config::read('template');
    }

    /**
     * Возвращает шаблонизатор для указанного объекта (контроллера/виджета)
     * @param \boolive\data\Entity $entity
     * @return
     */
    static function getEngine($entity)
    {
        $file = $entity->file();
        foreach (self::$engines as $pattern => $engine){
            if (fnmatch($pattern, $file)){
                if (is_string($engine)){
                    self::$engines[$pattern] = new $engine();
                }
                return self::$engines[$pattern];
            }
        }
        return null;
    }

    /**
     * Создание текста из шаблона
     * В шаблон вставляются переданные значения
     * При обработки шаблона могут довыбираться значения из $entity и создаваться команды в $commands
     * @param \boolive\data\Entity $entity
     * @param array $v
     * @throws Error
     * @return string
     */
    static function render($entity, $v = array())
    {
        if ($engine = self::getEngine($entity)){
            return $engine->render($entity, $v);
        }else{
            throw new Error(array('Template engine for entity "%s" not found ', $entity->uri()));
        }
    }
}