<?php
/**
 * Класс запроса к БД в режиме отладки
 * Является обёрткой для PDOStatement, реализуя профилирование запросов, значений, вставляемых
 * в объект запроса методами bindValue() и bindParam()
 *
 * @version 1.0
 */
namespace Engine;

use PDO,
	PDOStatement,
	Engine\Trace,
	Engine\Benchmark;

class DebugPDOStatement{
	/** @var PDOStatement */
	private $stmt;
	private $values = array();
	private $info;

	function __construct($stmt){
		$this->stmt = $stmt;
		$this->info['sql'] = $stmt->queryString;
	}

	function __destruct(){
		unset($this->stmt);
		unset($this->values);
	}

	function execute($params = null){
		Trace::Groups('DB')->group('count')->set(Trace::Groups('DB')->group('count')->get()+1);
		Benchmark::Start('sql');
		$result = $this->stmt->execute($params);
		Trace::Groups('DB')->group('query')->group()->set(array(
			'sql' => $this->stmt->queryString,
			'values' => $params?$params:$this->values,
			'benchmark' => Benchmark::Stop('sql', true)
			)
		);
		return $result;
	}

	function bindValue($key, $value, $data_type = PDO::PARAM_STR){
		$this->values[$key] = $value;
		return $this->stmt->bindValue($key, $value, $data_type);
	}

	function bindParam($key, &$value, $data_type = PDO::PARAM_STR, $length = null){
		$this->values[$key] = $value;
		return $this->stmt->bindParam($key, $value, $data_type, $length);
	}

	function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0){
		return $this->stmt->fetch($fetch_style, $cursor_orientation, $cursor_offset);
	}

	function __call($method, $params){
		return call_user_func_array(array($this->stmt, $method), $params);
	}
}
