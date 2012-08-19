<?php
/**
 * Класс запроса к БД в режиме отладки
 * Является обёрткой для PDOStatement, реализуя профилирование запросов, значений, вставляемых
 * в объект запроса методами bindValue() и bindParam()
 *
 * @version 1.0
 * @link http://boolive.ru/createcms/working-with-databases
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\database;

use PDO,
    PDOStatement,
    Boolive\develop\Trace,
    Boolive\develop\Benchmark;

class DBStatementDebug
{
    /** @var PDOStatement */
    private $stmt;
    private $values = array();
    private $info;

    function __construct($stmt)
    {
        $this->stmt = $stmt;
        $this->info['sql'] = $stmt->queryString;
    }

    function __destruct()
    {
        unset($this->stmt);
        unset($this->values);
    }

    function execute($params = null)
    {
        Trace::groups('DB')->group('count')->set(Trace::groups('DB')->group('count')->get()+1);
        Benchmark::start('sql');
        $result = $this->stmt->execute($params);
        Trace::groups('DB')->group('query')->group($this->stmt->queryString)->group()->set(array(
            'sql' => $this->stmt->queryString,
            'values' => $params?$params:$this->values,
            'benchmark' => Benchmark::stop('sql', true)
            )
        );
        return $result;
    }

    function bindValue($key, $value, $data_type = PDO::PARAM_STR)
    {
        $this->values[$key] = $value;
        return $this->stmt->bindValue($key, $value, $data_type);
    }

    function bindParam($key, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
    {
        $this->values[$key] = $variable;
        return $this->stmt->bindParam($key, $variable, $data_type, $length);
    }

    function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        return $this->stmt->fetch($fetch_style, $cursor_orientation, $cursor_offset);
    }

    function __call($method, $params)
    {
        return call_user_func_array(array($this->stmt, $method), $params);
    }
}
