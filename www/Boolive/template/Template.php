<?php
/**
 * Шаблонизатор
 * "Мост" к конкретным шаблонизаторам. Выбор происходит автоматически по расширениям файлов-шаблонов
 * @link http://boolive.ru/createcms/making-page
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\template;

use Boolive\errors\Error,
	Boolive\data\Data;

class Template
{
    /** @const  Файл с ассоциациями расширений файлов на шаблонизаторы */
    const CONFIG_FILE = 'config.templates.php';
    /** @var array Массив названий классов шаблонизаторов */
    static private $engines;

    /**
     * Загрузка шаблонизаторов
     */
    static private function loadEngines()
    {
        if (!isset(self::$engines)){
            self::$engines = array();
            if (file_exists(DIR_SERVER.self::CONFIG_FILE)){
                include DIR_SERVER.self::CONFIG_FILE;
                if (isset($config)) self::$engines = $config;
            }
        }
    }

    /**
     * Возвращает шаблонизатор для указанного объекта (контроллера/виджета)
     * @param \Boolive\data\Entity $entity
     * @return
     */
    static function getEngine($entity)
    {
        self::loadEngines();
        $file = $entity->getFile();
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
     * @param \Boolive\data\Entity $entity
     * @param array $v
     * @throws Error
     * @return string
     */
    static function render($entity, $v = array())
    {
        if ($engine = self::getEngine($entity)){
            return $engine->render($entity, $v);
        }else{
            throw new Error(array('Template engine for entity "%s" not found ', $entity['uri']));
        }
    }
}