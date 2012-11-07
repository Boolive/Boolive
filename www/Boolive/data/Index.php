<?php
/**
 * Класс индекса
 *
 * @link http://boolive.ru/createcms/sectioning
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data;

abstract class Index
{
    public function __construct($config){}

    /**
     * Выбор объекта по его uri из индекса
     * Если подходящего индекса нет, то автоматически создаётся новый по родителю искомого объекта
     * @param string $uri URI объекта
     * @param string $lang Код языка из 3 символов. Если не указан, то выбирается общий
     * @param int $owner Код владельца. Если не указан, то выбирается общий
     * @param null|int $date Дата создания (версия). Если не указана, то выбирается актуальная
     * @param null|bool $is_history Объект в истории (true) или нет (false) или без разницы (null). Если указана дата, то всегда null
     * @return \Boolive\data\Entity|null
     */
    public function read($uri, $lang = '', $owner = 0, $date = null, $is_history = false)
    {

    }

    /**
     * Поиск объектов по условию
     * Поиск выполняется по индексу. Если его нет, то он автоматически создаётся
     * Создание индекса может быть затратной операцией
     * @param array $cond Условие поиска
     * @param string $keys Название атрибута, который использовать для ключей массива результата
     * @return array|int Массив объектов или их количество, в зависимости от условия поиска
     */
    public function select($cond, $keys)
    {

    }
}