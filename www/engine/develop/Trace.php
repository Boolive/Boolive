<?php
/**
 * Трассировщик
 *
 * @version	1.2
 */
namespace Engine{

	class Trace{
		/** @var string Форматированное значение */
		private $value;
		/** @var mixed Ключ трассировки (именование) */
		private $key;
		/** @var array Список всех трассировок */
		static private $trace_list = array();

		/**
		 * Конструктор объекта трассировки
		 * @param $key Ключ новой трассировки
		 * @param $value Значения для трассировки
		 */
		public function __construct($key, $value){
			$this->key = $key;
			$this->value = self::Format($value);
		}

		/**
		 * Возвращение значения трассировки
		 * @return string
		 */
		public function value(){
			return $this->value;
		}

		/**
		 * Возвращение ключа трассировки
		 * @return string
		 */
		public function key(){
			return $this->value;
		}

		/**
		 * Вывод трассировки в HTML формате
		 * @return \Engine\Trace Объект буфера
		 */
		public function out(){
			echo '<pre>== '.$this->key." ==<br>\n".$this->value.'</pre>';
			return $this;
		}

		/**
		 * Запись значения буфера в лог файл
		 * @return \Engine\Trace Объект буфера
		 */
		public function log(){
			error_log('== Trace "'.$this->key."\" ==\n".$this->value);
			return $this;
		}

		/**
		 * Создание трассировки
		 * @param mixed $value Значение для трассировки
		 * @param string|int $key Ключ трассировки
		 * @return \Engine\Trace Объект буфера
		 */
		static public function Add($value = null, $key = null){
			if (empty($key)) $key = uniqid();
			return self::$trace_list[$key] = new Trace($key, $value);
		}

		/**
		 * Получения трассировки
		 * @param null $key Ключ трассировки, если null, то возвращается массив из всех трассировок
		 * @return array|\Engine\Trace Объект трассировки
		 */
		static public function Get($key = null){
			if (isset($key)){
				if (isset(self::$trace_list[$key])) return (self::$trace_list[$key]);
				return new Trace($key, null);
			}else{
				return self::$trace_list;
			}
		}

		/**
		 * Удаление трассировки
		 * @param null $key Ключ трассировки, если null, то очищается весь список тарссировок
		 */
		static public function Delete($key = null){
			if (isset($key)){
				if (isset(self::$trace_list[$key])) unset(self::$trace_list[$key]);
			}else{
				self::$trace_list = array();
			}
		}

		/**
		 * Форматирование значения
		 *
		 * @param mixed $var Значение для форматировния
		 * @param int $indent Отступ выводимой структуры
		 * @param array $trace_buf Буфер вывода (результата)
		 * @return string
		 */
		static public function Format(&$var, $indent = 0, &$trace_buf = array()){
			$sp = '| ';
			$sp2 = '. ';
			$out = '';
			// если не определена или null
			if (!isset($var) || is_null($var)){
				$out.= "null";
			}else
			// если булево
			if (is_bool($var)){
				$out.= $var ? 'true' : 'false';
			}else
			// если ресурс
			if (is_resource($var)){
				$out.= '{resource}';
			}else
			// если массив
			if (is_array($var)){
				if (count($var) == 0){
					$out.='{Array} ()';
				}else{
					$out.= '{Array}';
					foreach ($var as $name => $value){
						$out.= "\n".str_repeat($sp, $indent).$sp2.'['.$name.'] => '.self::Format($value, $indent+1, $trace_buf);
					}
				}
			}else
			// Если объект
			if (is_object($var)){
				$class_name = get_class($var);
				if (isset($trace_buf[spl_object_hash($var)])){
//					if ($var instanceof \Engine\Entity){
//						$list = array('id' => $var['id'], 'name'=> $var['name']);
//					}else{
						$list = array();
//					}
					$out.='{'.$class_name.'} уже отображен';
				}else{
					$trace_buf[spl_object_hash($var)] = true;
					$out.= '{'.$class_name.'}';
					while ($class_name = get_parent_class($class_name)){
						$out.= ' -> {'.$class_name.'}';
					}
					$list = self::ObjToArray($var);
				}

				if (count($list) > 0){
					foreach ($list as $name => $value){
						$out.= "\n".str_repeat($sp, $indent).$sp2.'['.$name.'] => '.self::Format($value, $indent+1, $trace_buf);
					}
				}
			}
			// Иначе
			else{
				$out.= htmlentities($var, ENT_QUOTES, 'UTF-8');
			}
			return $out;
		}

		/**
		 * Преобразование объекта в массив
		 * @param object|ITrace $object
		 * @return array
		 */
		static public function ObjToArray(&$object){
			if ($object instanceof ITrace){
				$arr = $object->trace();
			}else{
				$arr = (array)$object;
			}
			$result = array();
			while (list ($key, $value) = each($arr)){
				$keys = explode("\0", $key);
				$clear_key = $keys[count($keys) - 1];
				$result[$clear_key] = &$arr[$key];
			}
			return $result;
		}
	}

	/**
	 * Интерфейс для получения от объекта значений для трассировки (вывода)
	 */
	interface ITrace{
		/**
		 * @abstract
		 * @return array
		 */
		public function trace();
	}
}

namespace {
	/**
	 * Трассировка переменной с автоматическим выводом значения
	 * Сделано из-за лени обращаться к классу Trace :)
	 * @param mixed $var Значение для трассировки
	 * @param string|int $key Ключ трассировки
	 * @return \Engine\Trace Объект трассировки
	 */
	function trace($var = null, $key = null){
		return \Engine\Trace::Add($var, $key)->out();
	}
}
