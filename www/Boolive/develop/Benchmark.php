<?php
/**
 * Сбор статистики работы системы:
 * 1. Подсчет времени выполнеия
 * 2. Подсчет используемой оперативной памяти
 *
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\develop;

class Benchmark
{
    /** @var array Начальные значения замеров */
    static private $start = array();
    /** @var array Результаты замеров */
    static private $stop = array();

    /**
     * Активация
     * Запуск общего замера
     */
    static function activate()
    {
        self::start('all');
        self::$start['all']['time'] = $_SERVER['BOOLIVE_TIME'];
    }

    /**
     * Запуск замера
     * @param string $key Ключ замера. Если не указан, будет сгенерирован автоматически
     * @return string Ключ замера
     */
    static function start($key = null)
    {
        if (empty($key)) $key = uniqid();
        self::$start[$key] = array(
            'time' => microtime(true),
            'memory' => $key == 'all'?0:memory_get_usage()
        );
        return $key;
    }

    /**
     * Остановка замера и возвращение результатов
     * @param string $key Ключ замера
     * @param bool $format Признак, форматировать результат?
     * @return array Результаты замера
     */
    static function stop($key = 'all', $format = false)
    {
        self::$stop[$key] = array(
            'time' => microtime(true) - self::$start[$key]['time'],
            'memory' => memory_get_usage() - self::$start[$key]['memory'],
            'memory_max' => memory_get_peak_usage()
        );
        return self::get($key, $format);
    }

    /**
     * Информация о замере
     * @param string $key Ключ замера
     * @param bool $format Форматировать результат?
     * @return array Результаты замера
     */
    static function get($key = 'all', $format = false)
    {
        if (isset(self::$stop[$key])){
            if ($format){
                return array(
                    'time' => sprintf('%0.8f s', self::$stop[$key]['time']),
                    'memory' => sprintf('%0.3f kB', self::$stop[$key]['memory'] / 1024),
                    'memory_max' => sprintf('%0.3f kB', self::$stop[$key]['memory_max'] / 1024),
                );
            }else{
                return self::$stop[$key];
            }
        }
        return null;
    }

    /**
     * Остановка замера и вывод форматированного результата (html)
     * @param string $key
     */
    static function stopAndPrint($key = 'all')
    {
        if ($info = self::stop($key, true)){
            print "\n=== Benchmark \"".$key."\" ===\n";
            print 'Time: '.$info['time']."\n";
            print 'Memory: '.$info['memory']."\n";
            print 'Max memory: '.$info['memory_max']."\n";
        }
    }
}
