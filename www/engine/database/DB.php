<?php
/**
 * Класс доступа к реляционным базам данных.
 * Наследуется стандартный интерфейс доступа к данным PHP Data Objects (PDO)
 *
 * Особенности:
 * 1. Создание множества подключений без их дублирования
 * 2. Автоматическое присвоение префиксов к таблицам в SQL. Для этого имена таблиц дожны быть в фигурных скобках,
 * например, SELECT * FROM {news} WHERE {news}.id = 125
 * Вместе с префиксом добавляются обратные ковычки: `pfx_news`
 * 3. Псевдовложенные тарнзакции. Учитываются повторные запуски транзакций так, что реальное подтверждение или отмена
 *    выполняется только на первом уровне.
 * 4. Режим профилирования запросов. Информация записывается в модуль Trace
 *
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use PDO,
	PDOStatement,
	PDOException,
	Engine\F;

class DB extends PDO{
//	/** @const Файл параметров подключения по умолчанию */
//	const CONFIG_FILE = 'database/config.db.php';
//	/** @var array Параметры подключения по умолчанию */
//	static private $config;
	/** @var array Установленные соединения */
	static private $connection = array();


	/** @var array Уровни вложенности транзакций */
	private $transaction_level = 0;
	/** @var string Префикс к имени таблиц */
	private $prefix = '';
	/** @var bool Признак, включен или нет режим отладки */
	private $debug = false;

	/**
	 * Создание экземпляра DB, представляющего соединение с базой данных
	 * Если соединение с указанными параметрами уже существует, то оно будет возращено вместо создания нового
	 * @link http://php.net/manual/en/pdo.construct.php
	 * @param array|null $config Параметры подключения
	 * array(
	 * 	'dsn' => array('driver' => 'mysql', 'dbname' => '', ...),
	 *  'user' => '',
	 *  'password' => '',
	 *  'options' => array(),
	 *  'prefix' => ''
	 * )
	 * @return \Engine\SQL
	 */
	static function Connect($config = null){
//		if (empty($config)) $config = self::GetDefaultConfig();
		if (!empty($config['dsn'])){
			// Формирование DSN и других параметров подключения
			if (is_array($config['dsn'])){
				$dsn = $config['dsn']['driver'].':';
				unset($config['dsn']['driver']);
				foreach ($config['dsn'] as $name => $value){
					$dsn.=$name.'='.$value.';';
				}
			}else{
				$dsn = $config['dsn'];
			}
			if (empty($config['user'])) $config['user'] = null;
			if (empty($config['password'])) $config['password'] = null;
			if (empty($config['options'])) $config['options'] = null;
			if (empty($config['prefix'])) $config['prefix'] = '';
			if (empty($config['debug'])) $config['debug'] = false;
			// Ключ подключения
			$key = $dsn.'-'.$config['user'].'-'.$config['password'].'-'.$config['prefix'];
			if (!empty($config['options'])){
				$key.= serialize($config['options']);
			}
			// Если подключения нет, то создаём
			if (!isset(self::$connection[$key])){
				self::$connection[$key] = new self($dsn, $config['user'], $config['password'], $config['options'], $config['prefix'], $config['debug']);
			}
			return self::$connection[$key];
		}
		return null;
	}

//	/**
//	 * Параметры подключения по умолчанию
//	 * @return array|bool
//	 */
//	static function GetDefaultConfig(){
//		if (file_exists(ROOT_DIR_ENGINE.self::CONFIG_FILE)){
//			include ROOT_DIR_ENGINE.self::CONFIG_FILE;
//			if (isset($config)) self::$config = $config;
//		}else{
//			return false;
//		}
//		return self::$config;
//	}

	public function __construct($dsn, $username = null, $passwd = null, $options = array(), $prefix = '', $debug = false){
		parent::__construct($dsn, $username, $passwd, $options);
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->prefix = $prefix;
		$this->debug = $debug;
	}

	/**
	 * Старт транзакции. Учитывается вложенность транзакций
	 * @link http://www.php.net/manual/en/pdo.begintransaction.php
	 * @return bool
	 */
	public function beginTransaction(){
		if ($this->transaction_level++ == 0){
			return parent::beginTransaction();
		}else{
			return true;
		}
	}

	/**
	 * Фиксирование транзакции (выполненных запросов)
	 * @link http://www.php.net/manual/en/pdo.commit.php
	 * @return bool
	 */
	public function commit(){
		if ($this->transaction_level > 0){
			$this->transaction_level--;
			if ($this->transaction_level == 0){
				return parent::commit();
			}
			return true;
		}
		return false;
	}

	/**
	 * Отмена транзакции.
	 * @link http://www.php.net/manual/en/pdo.rollback.php
	 * @return bool
	 */
	public  function rollBack(){
		if ($this->transaction_level > 0){
			$this->transaction_level--;
			if ($this->transaction_level == 0){
				return parent::rollBack();
			}
			return true;
		}
		return false;
	}

	/**
	 * Проверка, открыта ли транзакция?
	 * @return bool
	 */
	public function isTransaction(){
		return $this->transaction_level > 0;
	}

	/**
	 * Выполнение запроса (не для выборок)
	 * @link http://www.php.net/manual/en/pdo.exec.php
	 * @param string $sql Строка SQL запроса
	 * @return int Количество затронутых запросом строк
	 */
	public function exec($sql){
		if ($this->debug){
			Trace::Groups('DB')->group('count')->set(Trace::Groups('DB')->group('count')->get()+1);
			Benchmark::Start('sql');
			$sql = self::AddPrefixes($sql);
			$result = parent::exec($sql);
			Trace::Groups('DB')->group('query')->group()->set(array(
				'sql' => $sql,
				'benchmark' => Benchmark::Stop('sql', true)
				)
			);
			return $result;
		}
		return parent::exec($this->addPrefixes($sql));
	}

	/**
	 * Подготовка запроса
	 * @link http://www.php.net/manual/en/pdo.prepare.php
	 * @param string $sql Строка SQL запроса с параметрами
	 * @param array $driver_options
	 * @throws Error
	 * @return \Engine\DebugSQLStatement|\PDOStatement
	 */
	public function prepare($sql, $driver_options = array()){
		if ($this->debug){
			$stmt = parent::prepare($this->addPrefixes($sql), $driver_options);
			if ($stmt instanceof PDOStatement){
				return new DebugSQLStatement($stmt);
			}else{
				throw new Error('PDO does not return PDOStatement');
			}
		}
		return parent::prepare($this->addPrefixes($sql), $driver_options);
	}

	/**
	 * Выполнение запроса с выборками
	 * @link http://www.php.net/manual/en/pdo.query.php
	 * @param string $sql Строка SQL запроса
	 * @return \PDOStatement
	 */
	public function query($sql){
		if ($this->debug){
			Trace::Groups('DB')->group('count')->set(Trace::Groups('DB')->group('count')->get()+1);
			Benchmark::Start('sql');
			$sql = self::AddPrefixes($sql);
			$result = parent::query($sql);
			Trace::Groups('DB')->group('query')->group()->set(array(
				'sql' => $sql,
				'benchmark' => Benchmark::Stop('sql', true)
				)
			);
			return $result;
		}
		return parent::query($this->addPrefixes($sql));
	}

	/**
	 * Добавление префиксов к именам таблиц в SQL
	 * Имена таблиц должы быть заключены в фигурные скобки без использования пробельных символов внутри
	 * @example SELECT * FROM {news} WHERE {news}.id = 125
	 * @param string $sql Строка запроса
	 * @return string Отформатированная строка запроса
	 */
	public function addPrefixes($sql){
		return strtr($sql, array(
			'{' => '`'.$this->prefix,
			'}' => '`'
		));
	}

	/**
	 * Простой парсер SQL-дампов для извлечения запросов
	 *
	 * @author Прибора Антон Николаевич (http://anton-pribora.ru)
	 * @copyright (c) Прибора Антон Николаевич, 2008-11-07
	 * @param $multisql Строка с множеством запросов. Дамп базы данных
	 * @return array Массив запросов
	 */
	static public function GetQueryList($multisql){
		$queries = array();
		$strlen = strlen($multisql);
		$position = 0;
		$query = '';

		for (; $position < $strlen; ++$position){
			$char = $multisql{$position};
			switch ($char){
				case '-':
					if (substr($multisql, $position, 3) !== '-- '){
						$query .= $char;
						break;
					}
				case '#':
					while ($char !== "\r" && $char !== "\n" && $position < $strlen - 1){
						$char = $multisql{++$position};
					}
					break;
				case '`':
				case '\'':
				case '"':
					$quote = $char;
					$query.= $quote;
					while ($position < $strlen - 1){
						$char = $multisql{++$position};
						if ($char === '\\'){
							$query.= $char;
							if ($position < $strlen - 1){
								$char = $multisql{++$position};
								$query.= $char;
								if ($position < $strlen - 1) $char = $multisql{++$position};
							}else{
								break;
							}
						}
						if ($char === $quote) break;
						$query .= $char;
					}
					$query.= $quote;
					break;

				case ';':
					$query = trim($query);
					if ($query) $queries[] = $query;
					$query = '';
					break;

				default:
					$query .= $char;
					break;
			}
		}
		$query = trim($query);
		if ($query) $queries[] = $query;
		return $queries;
	}

	/**
	 * Проверка системных требований для установки класса
	 * @return array
	 */
	static function SystemRequirements(){
		$requirements = array();
		if (!extension_loaded('pdo')){
			$requirements[] = 'Требуется расширение "pdo" для PHP';
		}
		return $requirements;
	}
}

