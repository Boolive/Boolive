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
namespace boolive\database;

use PDO,
    PDOStatement,
    boolive\develop\Trace,
    boolive\develop\Benchmark;

class DBStatementDebug
{
    /** @var PDOStatement */
    private $stmt;
    private $values = array();
    private $info;
    private $debug = true;
    private $count = true;
    private $slow = 0;

    function __construct($stmt, $debug = true, $count = true, $slow = 0)
    {
        $this->stmt = $stmt;
        $this->info['sql'] = $stmt->queryString;
        $this->debug = $debug;
        $this->count = $count;
        $this->slow = $slow;
    }

    function __destruct()
    {
        unset($this->stmt);
        unset($this->values);
    }

    function execute($params = null)
    {
        if ($this->count){
            Trace::groups('DB')->group('count')->set(Trace::groups('DB')->group('count')->get()+1);
        }
        if ($this->debug){
            Benchmark::start('sql');
            $result = $this->stmt->execute($params);
            //Trace::groups('Data')->group('')->set($this->stmt->queryString);
            $bm = Benchmark::stop('sql', true);
            if ($bm['time'] >= $this->slow){
                Trace::groups('DB')->group('query')->group($this->stmt->queryString)->group()->set(array(
                    'sql' => $this->stmt->queryString,
                    'values' => $params?$params:$this->values,
                    'benchmark' => $bm
                    )
                );
                Trace::groups('DB')->group('sql_time')->set(Trace::groups('DB')->group('sql_time')->get()+$bm['time']);
                Trace::groups('DB')->group('slow_count')->set(Trace::groups('DB')->group('slow_count')->get()+1);
            }
        }else{
            $result = $this->stmt->execute($params);
        }
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
