<?php
/**
 * Хранилище в MySQL
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace boolive\data\stores;

use boolive\auth\Auth,
    boolive\cache\Cache,
    boolive\database\DB,
    boolive\data\Entity,
    boolive\data\Data,
    boolive\functions\F,
    boolive\file\File,
    boolive\errors\Error,
    boolive\events\Events,
    boolive\develop\Trace;

class MySQLStore2 extends Entity
{
    /** @var \boolive\database\DB */
    public $db;

    /**
     * Конструктор экземпляра хранилища
     * @param array $config Параметры подключения к базе данных
     */
    function __construct($config)
    {
        $this->db = DB::connect($config);
        Events::on('Boolive::deactivate', $this, 'deactivate');
    }

    /**
     * Обработчик системного события deactivate (завершение работы системы)
     */
    function deactivate()
    {

    }

    /**
     * Чтение объектов
     * @param string|array $cond Условие на выбираемые объекты.
     * @param bool $index Признак, выполнять индексацию данных перед чтением или нет?
     * @return array|\boolive\data\Entity|null Массив объектов. Если глубина поиска ровна 0, то возвращается объект или null
     * @throws \Exception
     */
    function read($cond, $index = false)
    {
        return 'a';
    }
}