<?php
/**
 * Класс секции
 *
 * @link http://boolive.ru/createcms/sectioning
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data;

abstract class Section extends Entity
{
    /**
     * @param array $config Конфигурация соединения
     */
    public function __construct($config) {}

    /**
     * Выбор объекта по его uri
     * @param string $uri URI объекта
     * @param string $lang Код языка из 3 символов. Если не указан, то выбирается общий
     * @param int $owner Код владельца. Если не указан, то выбирается общий
     * @param null|int $date Дата создания (версия). Если не указана, то выбирается актуальная
     * @param null|bool $is_history Объект в истории (true) или нет (false) или без разницы (null). Если указана дата, то всегда null
     * @return \Boolive\data\Entity|null
     */
    public function read($uri, $lang = '', $owner = 0, $date = null, $is_history = false)
    {
        return null;
    }

    /**
     * Запись объекта. Путь указывается в атриубте uri объекта
     * @param \Boolive\data\Entity $entity Записываемый объект
     * @return bool Если были изменения, то true
     */
    public function put($entity)
    {
        return false;
    }

    /**
     * Вызов метода у объекта
     * Реализуется для внешних секций
     * @param $method
     * @param $args
     * @return mixed
     */
    public function call($method, $args)
    {
        return null;
    }

    /**
     * Выбор объектов по условию
     * @param array $cond Услвоие поиска
     * @return array
     */
    public function select($cond)
    {
        return array();
    }

    /**
     * Выбор количества бъектов по условию
     * @param array $cond Услвоие поиска
     * @return int
     */
    public function select_count($cond)
    {
        return 0;
    }
}
