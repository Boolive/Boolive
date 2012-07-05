<?php
/**
 * Фильтр и проверка значений
 *
 * - Используются классом \Engine\Values в соответствии с правилами \Engine\Rule для непосредственной
 *   проверки и фильтра значений.
 * - Для фильтра и проверки в соответствии с правилом применяется универсальный метод Check::Filter(),
 *   которым последовательно вызываются указанные в правиле соответствующие методы фильтров.
 *   Вызываются одноименные фильтрам методы класса Check, если их нет, то вызовится магический методы, которым
 *   сгенерируется событие для вызова внешнего метода фильтра.
 * - Если исходное значение отличается от отфильтрованного или не подлежит фильтру, то создаётся ошибка с сообщением,
 *   соответствующим названию примененного фильтра, например "max".
 * - Выполнение фильтров прекращается при возникновении первой ошибки. Но если специальным фильтром ignore определены
 *   игнорируемые ошибки, то выполнение следующих фильтров продолжается без создания ошибки, если текущая ошибка в
 *   списке игнорируемых.
 * - Если в правиле определено значение по умолчанию и имеется ошибка после всех проверок, то ошибка игнорируется
 *   и возвращается значение по умолчанию.
 * - В классе реализованы стандартные фильтры. Если в правиле определен нестандартный фильтр, то будет
 *   вызвано событие "Check::Filter_{name}", где {name} - название фильтра. Через событие будет предпринята попытка
 *   вызова внешней функции фильтра. Как создать свой фильтр написано в комментариях класса Rule.
 * - При проверки сложных структур, например массивов, объект ошибки будет вложенным. Вложенность ошибки соответствует
 *   вложенности правила.
 * - Ошибка - это объект класса \Engine\Error, являющийся наследником исключений. При возникновении ошибки
 *   исключения не выкидываются, а возвращается созданный объект исключения.
 *
 * @link http://boolive.ru/createcms/rules-for-filter (про правила и создание нового фильтра)
 * @version	2.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use Engine\Rule,
	Engine\Events;

class Check{
	/**
	 * Универсальный фильтр значения по правилу
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @return mixed
	 */
	static function Filter($value, Rule $rule, &$error = null){
		$result = null;
		$filters = $rule->getFilters();
		// Подготовка специальных Фильтров. Удаление из общего списка обработки
		if (isset($filters['required'])) unset($filters['required']);
		if (isset($filters['default']))	unset($filters['default']);
		if (isset($filters['ignore'])){
			$ignore = $filters['ignore'];
			if (sizeof($ignore) == 1 && is_array($ignore[0])) $ignore = $ignore[0];
			unset($filters['ignore']);
		}else{
			$ignore = array();
		}
		// Фильтр значений
		foreach ($filters as $filter => $args){
			$value = self::$filter($value, $error, $rule);
			if ($error){
				if ($ignore && in_array($error->getMessage(), $ignore)){
					$error = null;
				}else{
					break;
				}
			}
		}
		// Значение по умолчанию, если определено и имеется ошибка
		if ($error && isset($rule->default)){
			$error = null;
			$value = $rule->default[0];
		}
		return $value;
	}

	/**
	 * Вызов несуществующего фильтра
	 * @param $method Названия фильтра
	 * @param $args Аргументы для метода фильтра
	 * @return mixed
	 */
	static function __callStatic($method, $args){
		$result = Events::Send('Check::Filter_'.$method, $args);
		if ($result->count > 0){
			return $result->value;
		}else{
			return $args[0];
		}
	}

	/**
	 * Проверка и фильтр логического значения
	 * @param $value Значение для проверки и фильтра
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return bool
	 */
	static function bool($value, &$error, Rule $rule){
		if (is_string($value)){
			return !in_array(strtolower($value), array('false', 'off', 'no', '', '0'));
		}
		if (is_scalar($value)){
			return (bool)$value;
		}else{
			$error = new Error('bool');
			return false;
		}
	}

	/**
	 * Проверка и фильтр целого числа в диапазоне от -2147483648 до 2147483647
	 * @param $value Значение для проверки и фильтра
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return int|string
	 */
	static function int($value, &$error, Rule $rule){
		if (is_string($value)){
			$value = str_replace(' ', '', $value);
		}
		if (isset($value) && is_scalar($value) && preg_match('/^[-\+]?[0-9]+$/', strval($value)) == 1){
			return intval($value);
		}else{
			$error = new Error('int');
			return is_object($value)?1:intval($value);
		}
	}

	/**
	 * Проверка и фильтр действительного числа в диапазоне от -1.7976931348623157E+308 до 1.7976931348623157E+308
	 * @param $value Значение для проверки и фильтра
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return int|string
	 */
	static function double($value, &$error, Rule $rule){
		if (is_string($value)){
			$value = str_replace(' ', '', $value);
			$value = str_replace(',', '.', $value);
		}
		if (is_numeric($value)){
			return doubleval($value);
		}else{
			$error = new Error('double');
			return is_object($value)?1:doubleval($value);
		}
	}

	/**
	 * Проверка и фильтр строки
	 * @param $value Значение для проверки и фильтра
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return string
	 */
	static function string($value, &$error, Rule $rule){
		if (isset($value) && is_scalar($value)){
			return strval($value);
		}else{
			$error = new Error('string');
			return '';
		}
	}

	/**
	 * Проверка и фильтр NULL
	 * @param $value Значение для проверки и фильтра
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return null
	 */
	static function null($value, &$error, Rule $rule){
		if (isset($value) && !(is_string($value) && in_array(strtolower($value), array('null', '')))){
			$error = new Error('null');
		}
		return null;
	}

	/**
	 * Проверка и фильтр массива с учетом правил на его элементы
	 * @param $value Значение для проверки и фильтра
	 * @param null &$error Возвращаемый объект исключения, если элементы не соответсвуют правилам
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return array
	 */
	static function arrays($value, &$error, Rule $rule){
		$result = array();
		// Контейнер для ошибок на элементы
		$error = new Error('arrays');
		if (is_array($value)){
			// Сведения о правиле
			// Нормализация аргументов
			$rule_sub = isset($rule->arrays[0])? $rule->arrays[0] : array();
			$rule_default = isset($rule->arrays[1])? $rule->arrays[1] : null;
			$tree = !empty($rule->arrays[2]);
			if (!isset($rule->arrays[2]) && is_bool($rule_default)){
				$tree = $rule_default;
				$rule_default = null;
			}
			// Если не указаны вложенные правила, то аргументы смещаются влево
			if (is_null($rule_default) && $rule_sub instanceof Rule){
				$rule_default = $rule_sub;
			}
			if (!is_array($rule_sub)) $rule_sub = array();

			// Перебор и проверка с фильтром всех элементов
			foreach ((array)$value as $key => $v){
				$sub_error = null;
				if (isset($rule_sub[$key])){
					// Отсутствие элемента
					if (isset($rule_sub[$key]->forbidden)){
						$sub_error = new Error('forbidden');
					}else{
						$result[$key] = self::Filter($v, $rule_sub[$key], $sub_error);
					}
					unset($rule_sub[$key]);
				}else{
					if ($rule_default){
						$result[$key] = self::Filter($v, $rule_default, $sub_error);
					}
					// Если нет правила по умолчанию или оно не подошло и значение является массивом
					if (!$rule_default || $sub_error){
						// Если рекурсивная проверка вложенных массивов
						if ($tree && (is_array($v) || $v instanceof Values)){
							$sub_error = null;
							$result[$key] = self::Filter($v, $rule, $sub_error);
						}
					}
					// Если на элемент нет правила, то его не будет в результате
				}
				if ($sub_error){
					$error->{$key}->add($sub_error);
				}
			}
			// Перебор оставшихся правил, для которых не оказалось значений
			foreach ($rule_sub as $key => $rule){
				if (isset($rule->required) && !isset($rule->forbidden)){
					$result[$key] = self::Filter(null, $rule, $sub_error);
					if ($sub_error){
						$error->{$key}->add($sub_error);
					}
				}

			}
			// Если ошибок у элементов нет, то удаляем объект исключения
			if (!$error->isExist()){
				$error = null;
			}
		}
		return $result;
	}

	/**
	 * Проверка значения на соответствие объекту опредленного класса
	 * @param $value Значение для проверки
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return object|null
	 */
	static function object($value, &$error, Rule $rule){
		$class = isset($rule->object[0])? $rule->object[0] : null;
		if (is_object($value) && (empty($class) || $value instanceof $class)){
			return $value;
		}
		$error = new Error('object');
		return null;
	}

	/**
	 * Проверка значения на соответствие объекту класса \Engine\Values
	 * @param $value Значение для проверки
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return \Engine\Values Любое значение превразается в объект \Engine\Values
	 */
	static function values($value, &$error, Rule $rule){
		if ($value instanceof Values){
			return $value;
		}
		$error = new Error('values');
		return new Values($value);
	}

	/**
	 * Проверка значения на соответствие объекту класса \Engine\Entity
	 * Если значение строка, то значение будет воспринято как uri объекта данных, и будет попытка выбора объекта из бд.
	 * @param $value Значение для проверки
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return Entity|null
	 */
	static function entity($value, &$error, Rule $rule){
		$class = isset($rule->entity[0])? $rule->entity[0] : null;
		if (is_string($value)){
			// Пробуем получить объект по uri
			$value = Data::Object($value);
		}
		if ($value instanceof Entity && (empty($class) || $value instanceof $class)){
			// Вызов проверки средствами объекта

			return $value;
		}else{
			$error = new Error('entity');
			return null;
		}
	}

	/**
	 * Проверка и фильтр значения правилами на выбор.
	 * Если нет ни одного правила, то значение не проверяет и не фильтруется.
	 * Если ниодно правило не подходит, то возвращается ошибка и значение от последнего правила.
	 * @param $value Значение для проверки
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed|null
	 */
	static function any($value, &$error, Rule $rule){
		$rules = $rule->any;
		if (empty($rules)) return $value;
		if (sizeof($rules) == 1 && is_array($rules[0])) $rules = $rules[0];
		$result = null;
		foreach ($rules as $rule){
			$error = null;
			$result = self::Filter($value, $rule, $error);
			if (!$error) return $result;
		}
		return $result;
	}

	/**
	 * Максимально допустимое значение, длина или количество элементов. Правая граница отрезка
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function max($value, &$error, Rule $rule){
		$max = isset($rule->max[0])? $rule->max[0] : null;
		if (is_int($value) || is_double($value)){
			$result = min($max, $value);
		}else
		if (is_string($value) && mb_strlen($value) > $max){
			$result = mb_substr($value, 0, $max);
		}else
		if (is_array($value) && sizeof($value) > $max){
			$result = array_slice($value, 0, $max);
		}else{
			$result = $value;
		}
		if ($value != $result)	$error = new Error('max');
		return $result;
	}

	/**
	 * Минимально допустимое значение, длина или количество элементов. Левая граница отрезка
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function min($value, &$error, Rule $rule){
		$min = isset($rule->min[0])? $rule->min[0] : null;
		$result = $value;
		if (is_int($value) || is_double($value)){
			$result = max($min, $value);
			if ($value != $result)	$error = new Error('min');
		}else
		if (is_string($value) && mb_strlen($value) < $min){
			$error = new Error('min');
		}else
		if (is_array($value) && sizeof($value) < $min){
			$error = new Error('min');
		}
		return $result;
	}

	/**
	 * Меньше указанного значения, длины или количества элементов. Правая граница интервала
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function less($value, &$error, Rule $rule){
		$less = isset($rule->less[0])? $rule->less[0] : null;
		if ((is_int($value) || is_double($value)) && !($value < $less)){
			$result = $less - 1;
		}else
		if (is_string($value) && !(mb_strlen($value) < $less)){
			$result = mb_substr($value, 0, $less - 1);
		}else
		if (is_array($value) && !(sizeof($value) < $less)){
			$result = array_slice($value, 0, $less - 1);
		}else{
			$result = $value;
		}
		if ($value != $result)	$error = new Error('less');
		return $result;
	}

	/**
	 * Больше указанного значения, длины или количества элементов. Левая граница интервала
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function more($value, &$error, Rule $rule){
		$more = isset($rule->more[0])? $rule->more[0] : null;
		trace($more);
		if ((is_int($value) || is_double($value)) && !($value > $more)){
			$value = $more + 1;
			$error = new Error('more');
		}else
		if (is_string($value) && !(mb_strlen($value) > $more)){
			$error = new Error('more');
		}else
		if (is_array($value) && !(sizeof($value) > $more)){
			$error = new Error('more');
		}
		return $value;
	}

	/**
	 * Допустимые значения. Через запятую или массив
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function in($value, &$error, Rule $rule){
		$list = $rule->in;
		if (sizeof($list) == 1 && is_array($list[0])) $list = $list[0];
		if (!in_array($value, $list)){
			$value = null;
			$error = new Error('in');
		}
		return $value;
	}

	/**
	 * Недопустимые значения. Через запятую или массив
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function not_in($value, &$error, Rule $rule){
		$list = $rule->not_in;
		if (sizeof($list) == 1 && is_array($list[0])) $list = $list[0];
		if (in_array($value, $list)){
			$value = null;
			$error = new Error('not_in');
		}
		return $value;
	}

	/**
	 * Обрезание строки
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function trim($value, &$error, Rule $rule){
		if (is_scalar($value)){
			$result = trim($value);
			if ($result != $value) $error = new Error('trim');
			return $result;
		}
		return $value;
	}

	/**
	 * Экранирование html символов
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function escape($value, &$error, Rule $rule){
		if (is_scalar($value)){
			$result = htmlentities($value, ENT_QUOTES, 'UTF-8');
			if ($result != $value) $error = new Error('escape');
			return $result;
		}
		return $value;
	}

	/**
	 * Email адрес
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function email($value, &$error, Rule $rule){
		if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)){
			$error = new Error('email');
		}
		return $value;
	}

	/**
	 * URL
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function url($value, &$error, Rule $rule){
		if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_URL)){
			$error = new Error('url');
		}
		return $value;
	}

	/**
	 * URI = URL + URN
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function uri($value, &$error, Rule $rule){
		$check = $value;
		if (!preg_match('#^([^:/]+://).*$#iu', $check)){
			$check = 'http://'.trim($check, '/');
		}
		if (!is_string($value) || (trim($value, '/')!='' && !filter_var($check, FILTER_VALIDATE_URL))){
			$error = new Error('uri');
		}
		return $value;
	}

	/**
	 * IP
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function ip($value, &$error, Rule $rule){
		if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_IP)){
			$error = new Error('ip');
		}
		return $value;
	}

	/**
	 * Проверка на совпадение одному из регулярных выражений
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function regexp($value, &$error, Rule $rule){
		if (is_scalar($value)){
			$patterns = $rule->regexp;
			if (sizeof($patterns) == 1 && is_array($patterns[0])) $patterns = $patterns[0];
			foreach ($patterns as $pattern){
				if (preg_match($pattern, $value)) return $value;
			}
		}
		$error = new Error('regexp');
		return $value;
	}

	/**
	 * Проверка на совпадения одному из паттернов в стиле оболочки операционной системы: "*gr[ae]y"
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function ospatterns($value, &$error, Rule $rule){
		if (is_scalar($value)){
			$patterns = $rule->ospatterns;
			if (sizeof($patterns) == 1 && is_array($patterns[0])) $patterns = $patterns[0];
			foreach ($patterns as $pattern){
				if (fnmatch($pattern, $value)) return $value;
			}
		}
		$error = new Error('ospatterns');
		return $value;
	}

	/**
	 * HEX формат числа из 6 или 3 символов. Код цвета #FFFFFF. Возможны сокращения и опущение #
	 * @param $value Фильтруемое значение
	 * @param null &$error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @param \Engine\Rule $rule Объект правила. Аргументы одноименного фильтра применяются в методе
	 * @return mixed
	 */
	static function color($value, &$error, Rule $rule){
		if (is_scalar($value)){
			$value = trim($value, ' #');
			if (preg_replace('/^[0-9ABCDEF]{0,6}$/ui', '', $value) == '' && (strlen($value) == 6 || strlen($value) == 3)){
				return '#'.$value;
			}
		}
		$error = new Error('color');
		return '#000000';
	}
}