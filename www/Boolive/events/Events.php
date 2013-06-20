<?php
/**
 * Управление событиями
 *
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @todo При установке проверить возмоэжность записи в файл конфига
 */
namespace Boolive\events;

use Boolive\Boolive;

class Events
{
    /** @const Названия файла со сведениями о зарегистрированных обработчиков */
    const CONFIG_FILE = 'config.json';
    /** @var array Реестр обработчиков событий */
    private static $handlers = array();
    /** @var bool Признак, требуется ли выпонить сохранение обработчиков в файл */
    private static $need_save = false;

    /**
     * Активация модуля
     */
    static function activate()
    {
        self::load();
        self::on('Boolive::deactivate', '\Boolive\events\Events', 'deactivate', false);
    }

    /**
     * Обработчик системного события deactivate (завершение работы системы)
     */
    static function deactivate()
    {
        if (self::$need_save) self::save();
    }

    /**
     * Добавление обработчика события
     *
     * @param string $event_name Имя события
     * @param string $handler_module Имя класса обработчика события
     * @param string $handler_method Имя метода класса обработчика события
     * @param bool $save Признак, сохранять регистрацию на событие?
     * @param bool $once Признак, одноразовая обработка события или нет?
     */
    static function on($event_name, $handler_module, $handler_method, $save = false, $once = false)
    {
        self::$handlers[$event_name][] = array($handler_module, $handler_method, 'save' => $save, 'once' => $once);
        if ($save) self::$need_save = true;
    }

    /**
     * Удаление обработчика события
     *
     * @param string $event_name Имя события
     * @param string $handler_module Имя класса обработчика события
     * @param string $handler_method Имя метода модуля обработчика события
     */
    static function off($event_name, $handler_module, $handler_method)
    {
        if (isset(self::$handlers[$event_name])){
            $list = self::$handlers[$event_name];
            foreach ($list as $key => $handler){
                if (($handler[0] == $handler_module) && ($handler[1] == $handler_method)){
                    unset(self::$handlers[$event_name][$key]);
                    if (!empty($handler['svae'])) self::$need_save = true;
                }
            }
        }
    }

    /**
     * Генерация события
     *
     * @param string $event_name Имя события
     * @param array|mixed $params Параметры события
     * @return EventResult Объект события с результатами его обработки
     */
    static function trigger($event_name, $params=array())
    {
        $r = new EventResult();
        if (isset(self::$handlers[$event_name])){
            $cnt = sizeof(self::$handlers[$event_name]);
            for ($i = 0; $i < $cnt; $i++){
                if (!is_array($params)){
                    $params = array($params);
                }
                if (is_string(self::$handlers[$event_name][$i][0]) && !Boolive::isIncluded(self::$handlers[$event_name][$i][0])){
                    Boolive::activate(self::$handlers[$event_name][$i][0]);
                }
                if (method_exists(self::$handlers[$event_name][$i][0], self::$handlers[$event_name][$i][1])){
                    $value = call_user_func_array(array(self::$handlers[$event_name][$i][0], self::$handlers[$event_name][$i][1]), $params);
                    if (isset($value)) $r->result = $value;
                    $r->count++;
                    if (!empty(self::$handlers[$event_name]['once'])){
                        self::off($event_name, self::$handlers[$event_name][$i][0], self::$handlers[$event_name][$i][1]);
                    }
                }
            }
        }
        return $r;
    }

    /**
     * Загрузка реестра обработчиков событий
     */
    private static function load()
    {
        $content = file_get_contents(DIR_SERVER_ENGINE.'events/'.self::CONFIG_FILE);
        self::$handlers = json_decode($content, true);
    }

    /**
     * Сохранение реестра обработчиков событий
     */
    private static function save()
    {
        $content = array();
        $list = self::$handlers;
        foreach ($list as $event => $handlers){
            $content[$event] = array();
            $cnt = sizeof($handlers);
            for ($i = 0; $i < $cnt; $i++){
                // Если не указано о сохранени или явно указано сохранять
                if (!isset($handlers[$i]['save']) || !empty($handlers[$i]['save'])){
                    $content[$event][] = $handlers[$i];
                }
            }
            if (empty($content[$event])) unset($content[$event]);
        }
        $content = json_encode($content);
        if ($f = fopen(DIR_SERVER_ENGINE.'events/'.self::CONFIG_FILE, 'w')){
            fwrite($f, $content);
            fclose($f);
        }
        self::$need_save = false;
    }
}