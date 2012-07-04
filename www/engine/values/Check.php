<?php
/**
 * Фильтры и валидаторы значений
 *
 * - Используются классом \Engine\Value в соответствии с правилами \Engine\Rule для непосредственной
 *   проверки и фильтра значений.
 * - Для фильтра и проверки в соответсвии с правилом применяется универсальный метод Check::Filter
 * - Если тип значения не соответсвует указанному в правиле, и невозможно привести его к нужному типу без искожения
 *   значения, то создаётся объект ошибки с сообщением, соответсвующим названию нужного типа значения.
 * - Если тип значения соответсвует правилу, то выполняются фильтры над значением, если они определены в правиле
 * - Если исходное значение отличается от отфильтрованного, то создаётся ошибка с сообщением, соответсвующим
 *   названию примененного фильтра, например "max".
 * - Выполнение фильтров прекращается при возникновении первой ошибки. Но если специальным фильтром ignore опредлены
 *   игнорируемые ошибки, то может продолжится выполнение следующих фильтров без создания ошибки.
 * - Если в правиле определено значение по умолчанию и имеется ошибка после всех проверок, то ошибка игнорируется
 *   и возвращается значание по умолчанию.
 * - В классе реализованы стандартные фильтры. Если в правиле опредлен нестанадртный фильтр, то будет
 *   создано событие "Check::Filter_{name}", где {name} - навзание фильтра. Через событие будет попытка
 *   вызова внешней функции фильтра.
 * - При проверки сложных структур, например многомерных массивов, объект ошибки будет таким же многомерным.
 * - Ошибка - это объект класса \Engine\Error, являющийся наследником исключений. Но при возникновении ошибок
 *   исключения не выкидываются, а только возвращается их объект.
 *
 * @version	1.1
 * @link http://boolive.ru/createcms/filter-and-check-data
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use Engine\Rule;

class Check{
	/**
	 * Фильтр значения по правилу
	 * @param $value
	 * @param $rule
	 * @param null $error
	 * @return mixed
	 */
	static function Filter($value, Rule $rule, &$error = null){
		$result = null;
		// Подготовка специальных Фильтров
		$filters = $rule->getFilters();
		if (isset($filters['required'])) unset($filters['required']);
		if (isset($filters['default']))	unset($filters['default']);
		if (isset($filters['ignore'])){
			$ignore = $filters['ignore'][0];
			if (!is_array($ignore)) $ignore = array($ignore);
			unset($filters['ignore']);
		}else{
			$ignore = array();
		}
		// Проверка типа значения
		switch ($rule->getType()){
			case Rule::TYPE_BOOL: $result = self::Bool($value, $error); break;
			case Rule::TYPE_INT: $result = self::Int($value, $error); break;
			case Rule::TYPE_DOUBLE: $result = self::Double($value, $error); break;
			case Rule::TYPE_STRING: $result = self::String($value, $error); break;
			case Rule::TYPE_NULL: $result = self::Null($value, $error); break;
			case Rule::TYPE_ARRAY: $result = self::Arrays($value, $rule, $error); break;
			case Rule::TYPE_OBJECT: $result = self::Object($value, $rule->getClass(), $error); break;
			case Rule::TYPE_VALUES: $result = self::Values($value, $error); break;
			case Rule::TYPE_ENTITY: $result = self::Entity($value, $rule->getClass(), $error); break;
			case Rule::TYPE_ANY: $result = self::Any($value, $rule->getRuleSub(), $error); break;
			default:
				$error = new Error('UNKNOWN_TYPE');
		}
		if (!$error){
			// Фильтр значений
			foreach ($filters as $filter => $args){
				$args[] = $result;
				$args[] = &$error;
				$result = call_user_func_array(array('\Engine\Check', $filter), $args);
				if ($error){
					if ($ignore && in_array($error->getMessage(), $ignore)){
						$error = null;
					}else{
						break;
					}
				}
			}
		}
		// Значение по умолчанию, если опредлено и имеется ошибка
		if ($error && $rule->isFilterExist('default')){
			$error = null;
			$result = $rule->getFilter('default');
			$result = $result[0];
		}
		return $result;
	}
	#########################################
	#										#
	#		Методы проверки типа 			#
	#										#
	#########################################
	/**
	 * Проверка и фильтр логического значения
	 * @param $value Значение для проверки и фильтра
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @return bool
	 */
	static function Bool($value, &$error = null){
		if (is_string($value)){
			return !in_array(strtolower($value), array('false', 'off', 'no', '', '0'));
		}
		if (is_scalar($value)){
			return (bool)$value;
		}else{
			$error = new Error('NOT_BOOL');
			return false;
		}
	}

	/**
	 * Проверка и фильтр целого числа в диапазоне от -2147483648 до 2147483647
	 * @param $value Значение для проверки и фильтра
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @return int|string
	 */
	static function Int($value, &$error = null){
		if (is_string($value)){
			$value = str_replace(' ', '', $value);
		}
		if (isset($value) && is_scalar($value) && preg_match('/^[-\+]?[0-9]+$/', strval($value)) == 1){
			return intval($value);
		}else{
			$error = new Error('NOT_INT');
			return is_object($value)?1:intval($value);
		}
	}

	/**
	 * Проверка и фильтр действительного числа в диапазоне от -1.7976931348623157E+308 до 1.7976931348623157E+308
	 * @param $value Значение для проверки и фильтра
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @return int|string
	 */
	static function Double($value, &$error = null){
		if (is_string($value)){
			$value = str_replace(' ', '', $value);
			$value = str_replace(',', '.', $value);
		}
		if (is_numeric($value)){
			return doubleval($value);
		}else{
			$error = new Error('NOT_DOUBLE');
			return is_object($value)?1:doubleval($value);
		}
	}

	/**
	 * Проверка и фильтр строки
	 * @param $value Значение для проверки и фильтра
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @return string
	 */
	static function String($value, &$error = null){
		if (isset($value) && is_scalar($value)){
			return strval($value);
		}else{
			$error = new Error('NOT_STRING');
			return '';
		}
	}

	/**
	 * Проверка и фильтр NULL
	 * @param $value Значение для проверки и фильтра
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @return null
	 */
	static function Null($value, &$error = null){
		if (isset($value) && !(is_string($value) && in_array(strtolower($value), array('null', '')))){
			$error = new Error('NOT_NULL');
		}
		return null;
	}

	/**
	 * Проверка и фильтр массива с учетом правил на его элементы
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило на массив
	 * @param null $error Возвращаемый объект исключения, если элементы не соответсвуют правилам
	 * @return array
	 */
	static function Arrays($value, Rule $rule, &$error = null){
		$result = array();
		// Любое значение превращаем в \Engine\Values
		if (!$value instanceof Values)	$value = new Values($value);
		// Сведения о правиле
		$rule_sub = $rule->getRuleSub();
		$rule_default = $rule->getRuleDefault();
		$tree = $rule->getRecursive();
		// Контейнер для ошибок на элементы
		$error = new Error();
		// Перебор и проверка с фильтром всех элементов
		foreach ((array)$value as $key => $v){
			$sub_error = null;
			if (isset($rule_sub[$key])){
				// Отсутствие значения
				if ($rule->getType() == Rule::TYPE_FORBIDDEN){
					$sub_error = new Error('NOT_FORBIDDEN');
				}else{
					$result[$key] = Check::Filter($v, $rule_sub[$key], $error);
				}
				unset($rule_sub[$key]);
			}else{
				if ($rule_default){
					$result[$key] = $value->get($key, $rule_default, $sub_error);
				}
				// Если нет правила по умолчанию или оно не подошло и значение является массивом
				if (!$rule_default || $sub_error){
					// Если рекурсивная проверка вложенных массивов
					if ($tree && (is_array($v) || $v instanceof Values)){
						$sub_error = null;
						$result[$key] = $value->get($key, $rule, $sub_error);
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
			if ($rule->isFilterExist('required') && $rule->getType()!=Rule::TYPE_FORBIDDEN){
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
		return $result;
	}

	/**
	 * Проверка значения на соответствие объекту опредленного класса
	 * @param $value Значение для проверки
	 * @param null $class Требуемый класс объекта
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @return object|null
	 */
	static function Object($value, $class = null, &$error = null){
		if (is_object($value) && (empty($class) || $value instanceof $class)){
			return $value;
		}
		$error = new Error('NOT_OBJECT');
		return null;
	}

	/**
	 * Проверка значения на соответствие объекту класса \Engine\Values
	 * @param $value Значение для проверки
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @return \Engine\Values Любое значение превразается в объект \Engine\Values
	 */
	static function Values($value, &$error = null){
		if ($value instanceof Values){
			return $value;
		}
		$error = new Error('NOT_VALUES');
		return new Values($value);
	}

	/**
	 * Проверка значения на соответствие объекту класса \Engine\Entity
	 * Если значение строка, то значение будет воспринято как uri объекта данных, и будет попытка выбора объекта из бд.
	 * @param $value Значение для проверки
	 * @param null $class Класс наследник \Engine\Entity
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует типу
	 * @return Entity|null
	 */
	static function Entity($value, $class = null, &$error = null){
		if (is_string($value)){
			// Пробуем получить объект по uri
			$value = Data::Object($value);
		}
		if ($value instanceof Entity && (empty($class) || $value instanceof $class)){
			// Вызов проверки средствами объекта

			return $value;
		}else{
			$error = new Error('NOT_ENTITY');
			return null;
		}
	}

	/**
	 * Проверка и фильтр значения правилами на выбор.
	 * Если нет ни одного правила, то значение не проверяет и не фильтруется.
	 * Если ниодно правило не подходит, то возвращается ошибка и значение от последнего правила.
	 * @param $value Значение для проверки
	 * @param array $rules Объекты правил
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed|null
	 */
	static function Any($value, $rules = array(), &$error = null){
		if (empty($rules)) return $value;
		$result = null;
		foreach ($rules as $rule){
			$error = null;
			$result = self::Filter($value, $rule, $error);
			if (!$error) return $result;
		}
		return $result;
	}

	#########################################
	#										#
	#		Методы фильтра значения			#
	#										#
	#########################################
	/**
	 * Вызов несуществующего фильтра
	 * @param $method Названия фильтра
	 * @param $args Аргшументы фильтра
	 * @return mixed|null
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
	 * Максимально допустимое значение, длина или количество элементов. Правая граница отрезка
	 * @param $max
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function max($max, $value, &$error = null){
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
	 * @param $min
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function min($min, $value, &$error = null){
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
	 * @param $less
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function less($less, $value, &$error = null){
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
	 * @param $more
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function more($more, $value, &$error = null){
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
	 * @param array $list Список допустимых значений
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function in($list, $value, &$error = null){
		if (!is_array($list)) $list = array($list);
		if (!in_array($value, $list)){
			$value = null;
			$error = new Error('in');
		}
		return $value;
	}

	/**
	 * Недопустимые значения. Через запятую или массив
	 * @param array $list Список недопустимых значений
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function not_in($list, $value, &$error = null){
		if (!is_array($list)) $list = array($list);
		if (in_array($value, $list)){
			$value = null;
			$error = new Error('not_in');
		}
		return $value;
	}

	/**
	 * Обрезание строки
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function trim($value, &$error = null){
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
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function escape($value, &$error = null){
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
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function email($value, &$error = null){
		if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)){
			$error = new Error('email');
		}
		return $value;
	}

	/**
	 * URL
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function url($value, &$error = null){
		if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_URL)){
			$error = new Error('url');
		}
		return $value;
	}

	/**
	 * URI = URL + URN
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function uri($value, &$error = null){
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
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function ip($value, &$error = null){
		if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_IP)){
			$error = new Error('ip');
		}
		return $value;
	}

	/**
	 * Проверка на совпадение одному из регулярных выражений
	 * @param array|string $patterns Строка или массив регулярных выражений
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function regexp($patterns, $value, &$error = null){
		if (!is_array($patterns)) $patterns = array($patterns);
		foreach ($patterns as $pattern){
			if (preg_match($pattern, $value)) return $value;
		}
		$error = new Error('regexp');
		return $value;
	}

	/**
	 * Проверка на совпадения одному из паттернов в стиле оболочки операционной системы: "*gr[ae]y"
	 * @param array|string $patterns Строка или массив паттернов в стиле *gr[ae]y?
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function filenames($patterns, $value, &$error = null){
		if (!is_array($patterns)) $patterns = array($patterns);
		foreach ($patterns as $pattern){
			if (fnmatch($pattern, $value)) return $value;
		}
		$error = new Error('filenames');
		return $value;
	}

	/**
	 * HEX формат числа из 6 или 3 символов. Код цвета #FFFFFF. Возможны сокращения и опущение #
	 * @param $value Фильтруемое значение
	 * @param null $error Возвращаемый объект исключения, если значение не соответсвует правилу
	 * @return mixed
	 */
	static function color($value, &$error = null){
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