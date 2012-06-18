<?php
/**
 * Фильтры и валидаторы значений
 *
 * @version	1.1
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;

use Engine\Rule;

class Check{
	/** @var Правило по умолчанию.*/
	static private $default_rule;

	/**
	 * Правило проверки по умолчанию
	 * @return mixed
	 */
	static function GetDefaultRule(){
		if (!isset(self::$default_rule)){
			self::$default_rule = Rule::No();
		}
		return self::$default_rule;
	}

	/**
	 * Фильтр/проверка значения
	 * Вызов метода проверки в соответсвии с переданным правилом
	 * Внешние методы проверки вызываются по событию "Check::Filter_{type}", {type} - тип правила
	 * @param mixed $value Фильтруемое значение
	 * @param \Engine\Rule $rule Правило проверки/фильтра
	 * @param bool $used_filter
	 * @return mixed|null
	 */
	static function Filter($value, $rule, &$used_filter = false){
		if (($list = $rule->getIn()) && !in_array($value, $list)){
			$used_filter = Rule::ERROR_IN;
		}else
		if (($list = $rule->getNotIn()) && in_array($value, $list)){
			$used_filter = Rule::ERROR_NOT_IN;
		}

		if (!$used_filter){
			switch ($rule->getType()){
				case Rule::TYPE_STRING: return Check::String($value, $rule, $used_filter);
				case Rule::TYPE_INT32: return Check::Int32($value, $rule, $used_filter);
				case Rule::TYPE_UINT32: return Check::UInt32($value, $rule, $used_filter);
				case Rule::TYPE_NO: return $value;
				case Rule::TYPE_BOOL: return Check::Bool($value, $rule, $used_filter);
				case Rule::TYPE_INT8: return Check::Int8($value, $rule, $used_filter);
				case Rule::TYPE_UINT8: return Check::UInt8($value, $rule, $used_filter);
				case Rule::TYPE_INT64: return Check::Int64($value, $rule, $used_filter);
				case Rule::TYPE_UINT64: return Check::UInt64($value, $rule, $used_filter);
				case Rule::TYPE_DOUBLE: return Check::Double($value, $rule, $used_filter);
				case Rule::TYPE_TEXT: return Check::Text($value, $rule, $used_filter);
				case Rule::TYPE_BIGTEXT: return Check::BigText($value, $rule, $used_filter);
				case Rule::TYPE_SYSNAME: return Check::SysName($value, $rule, $used_filter);
				case Rule::TYPE_EMAIL: return Check::Email($value, $rule, $used_filter);
				case Rule::TYPE_COLOR: return Check::Color($value, $rule, $used_filter);
				case Rule::TYPE_REG_EXP: return Check::RegExp($value, $rule, $used_filter);
				case Rule::TYPE_OBJECT:
					if ($class = $rule->getClass()){
						if ($value instanceof $class) return $value;
					}else{
						if (is_object($value)) return $value;
					}
					$used_filter = Rule::ERROR_TYPE;
					return null;
				case Rule::TYPE_ENTITY:
//					if (empty($value)) return null;
//					// Определение объекта через его идентификатор и секцию
//					if ((is_array($value)||$value instanceof Values) && !empty($value['section'])){
//						if (empty($value['id'])){
//							$value = Data::Get($value['section']);
//						}else{
//							$value = Data::Get($value['section'], $value['id']);
//						}
//					}else
//					// Определение объекта через путь на него. Объект может быть виртуальным
//					if (is_string($value)){
//						$value = Data::GetByPath($value, false);
//					}
//					$class = $rule->getClass();
//					if (!($value instanceof $class)){
//						$value = null;
//						$used_filter = Rule::ERROR_TYPE;
//					}
//					return $value;
				default:
					// Внешний фильтр
					$result = Events::Send('Check::Filter_'.$rule->getType(), array($value, $rule, &$used_filter));
					if ($result->count > 0){
						return $result->value;
					}else{
						$used_filter = Rule::ERROR_UNKNOWN_TYPE;
					}
			}
		}
		return null;
	}

	/**
	 * Проверка, является ли значение представлением целого числа
	 * @param mixed $value
	 * @return bool
	 */
	static function IsInt($value){
		return preg_match('/^[-\+]?[0-9]+$/', strval($value)) == 1;
	}

	/**
	 * Проверка, является ли значение представлением действительного числа
	 * @param mixed $value
	 * @return bool
	 */
	static function IsDouble($value){
		return (preg_match('/^[-\+]?[0-9]+([\.]?[0-9]+)?$/', strval($value)) == 1);
	}
	
	/**
	 * Проверка и фильтр логического значения - превращение в целое 0 или 1
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return int|null
	 */
	static function Bool($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		if ($rule->getCanEmpty() && (is_null($value) || $value=='')){
			return null;
		}
		$used_filter = false;
		if (is_string($value)){
			switch (strtolower($value)){
				case 'false':
				case 'off':
				case 'no':
				case '':
				case '0':
					$value = 0;
					break;
				default:
					$value = 1;
					break;
			}
		}
		if (is_bool($value) || self::IsInt($value)){
			$value = $value ? 1 : 0;
		}else{
			$value = 0;
			$used_filter = Rule::ERROR_TYPE;
		}
		return $value;
	}

	/**
	 * Проверка и фильтр целого числа
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return mixed|null
	 */
	private static function Int($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		if ($rule->getCanEmpty() && (is_null($value) || $value==='')){
			return null;
		}
		$used_filter = false;
		$max = is_null($rule->getMax()) ? 18446744073709500000 : min($rule->getMax(), 18446744073709500000);
		$min = is_null($rule->getMin()) ? -9223372036854770000 : max($rule->getMin(), -9223372036854770000);
		if (is_string($value)){
			$value = str_replace(' ', '', $value);
		}
		if (self::IsInt($value) && $max >= $min){
			$v = min($max, max($min, intval($value)));
			// Фиксируем использованный фильтр
			if ($v > $value) $used_filter = Rule::ERROR_MIN;
			if ($v < $value) $used_filter = Rule::ERROR_MAX;
			// Больше чем..
			$more = $rule->getMore();
			if (isset($more) && !($value > $more)){
				$v = $more+1;
				$used_filter = Rule::ERROR_MORE;
			}
			// Меньше чем..
			$less = $rule->getLess();
			if (isset($less) && !($value < $less)){
				$v = $more-1;
				$used_filter = Rule::ERROR_LESS;
			}
			return $v;
		}
		$used_filter = Rule::ERROR_TYPE;
		return max($min, 0);
	}

	/**
	 * Проверка и фильтр целого 8-битного числа
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return int|null
	 */
	static function Int8($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		$rule->max((is_null($rule->getMax()))?127:min($rule->getMax(), 127));
		$rule->min((is_null($rule->getMIn()))?-128:max($rule->getMin(), -128));
		return self::Int($value, $rule, $used_filter);
	}

	/**
	 * Проверка и фильтр целого 8-битного безнакового числа
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return int|null
	 */
	static function UInt8($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		$rule->max((is_null($rule->getMax()))?255:min($rule->getMax(), 255));
		$rule->min((is_null($rule->getMIn()))?0:max($rule->getMin(), 0));
		return self::Int($value, $rule, $used_filter);
	}

	/**
	 * Проверка и фильтр целого 32-битного числа
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return mixed|null
	 */
	static function Int32($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		$rule->max((is_null($rule->getMax()))?2147483647:min($rule->getMax(), 2147483647));
		$rule->min((is_null($rule->getMIn()))?-2147483648:max($rule->getMin(), -2147483648));
		return self::Int($value, $rule, $used_filter);
	}

	/**
	 * Проверка и фильтр целого 32-битного безнакового числа
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return mixed|null
	 */
	static function UInt32($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		$rule->max((is_null($rule->getMax()))?4294967295:min($rule->getMax(), 4294967295));
		$rule->min((is_null($rule->getMIn()))?0:max($rule->getMin(), 0));
		return self::Int($value, $rule, $used_filter);
	}

	/**
	 * Проверка и фильтр целого 64-битного числа
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return mixed|null
	 */
	static function Int64($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		$rule->max((is_null($rule->getMax()))?9223372036854770000:min($rule->getMax(), 9223372036854770000));
		$rule->min((is_null($rule->getMIn()))?-9223372036854770000:max($rule->getMin(), -9223372036854770000));
		return self::Int($value, $rule, $used_filter);
	}

	/**
	 * Проверка и фильтр целого 64-битного безнакового числа
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return mixed
	 */
	static function UInt64($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		$rule->max((is_null($rule->getMax()))?18446744073709500000:min($rule->getMax(), 18446744073709500000));
		$rule->min((is_null($rule->getMIn()))?0:max($rule->getMin(), 0));
		return self::Int($value, $rule, $used_filter);
	}

	/**
	 * Проверка и фильтр действительного числа
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return float|mixed|null
	 */
	static function Double($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		if ($rule->getCanEmpty() && (is_null($value) || $value==='')){
			return null;
		}
		$used_filter = false;
		$max = is_null($rule->getMax()) ? 1.7976931348623157E+308 : min($rule->getMax(), 1.7976931348623157E+308);
		$min = is_null($rule->getMin()) ? -1.7976931348623157E+308 : max($rule->getMin(), -1.7976931348623157E+308);
		if (is_string($value)){
			$value = str_replace(' ', '', $value);
			$value = str_replace(',', '.', $value);
		}
		if (self::IsDouble($value) && $max >= $min){
			$v = floatval(min($max, max($min, $value)));
			// Фиксируем использованный фильтр
			if ($v > $value) $used_filter = Rule::ERROR_MIN;
			if ($v < $value) $used_filter = Rule::ERROR_MAX;
			// Больше чем..
			$more = $rule->getMore();
			if (isset($more) && !($value > $more)){
				$v = $more+1;
				$used_filter = Rule::ERROR_MORE;
			}
			// Меньше чем..
			$less = $rule->getLess();
			if (isset($less) && !($value < $less)){
				$v = $more-1;
				$used_filter = Rule::ERROR_LESS;
			}
			return $v;
		}
		$used_filter = Rule::ERROR_TYPE;
		return max($min, 0);
	}

	/**
	 * Проверка и фильтр HEX значения цвета
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return null|string
	 */
	static function Color($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		if ($rule->getCanEmpty() && (is_null($value) || $value=='')){
			return null;
		}
		$used_filter = false;
		if (isset($value) && is_scalar($value)){
			$value = trim($value, ' #');
		}
		if (!(preg_replace('/^[0-9ABCDEF]{0,6}$/ui', '', $value) == '' && (strlen($value) == 6 || strlen($value) == 3))){
			$used_filter = Rule::ERROR_TYPE;
			return '#000000';
		}
		return '#'.$value;
	}

	/**
	 * Проверка e-mail адреса
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return null|string
	 * @todo Учесть национальные домены
	 */
	static function Email($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		$value = Check::String($value, $rule, $used_filter);
		if ($used_filter){
			// Проверка e-mail
			if (!$used_filter && !filter_var($value, FILTER_VALIDATE_EMAIL)){
				$used_filter = Rule::ERROR_TYPE;
				$value = '';
			}
		}
		return $value;
	}

	/**
	 * Проверка и фильтр строки
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return string
	 */
	static function String($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
	 	if ($rule->getCanEmpty() && (is_null($value) || $value=='')){
			return null;
		}
		$used_filter = false;
		$max = is_null($rule->getMax()) ? 255 : $rule->getMax();
		$min = is_null($rule->getMin()) ? 0 : max($rule->getMin(), 0);

		if (isset($value) && is_scalar($value) && $max >= $min){
			if ($chars = $rule->getArgs('trim')){
				$value = trim($value, $chars);
			}else
			if (!$rule->getArgs('not-trim')){
				$value = trim($value);
			}
			// Проверка максимальной длины
			$v = ($max == 0) ? '' : mb_substr($value, 0, $max);
			if ($v != $value){
				$used_filter = Rule::ERROR_MAX;
				$value = $v;
			}
			$len = mb_strlen($value);
			// Проверка минимальной длины
			if ($len < $min){
				$used_filter = Rule::ERROR_MIN;
			}
			// Больше чем..
			$more = $rule->getMore();
			if (isset($more) && $len <= $more){
				$value = '';
				$used_filter = Rule::ERROR_MORE;
			}
			// Меньше чем..
			$less = $rule->getLess();
			if (isset($less) && $len >= $less){
				$value = mb_substr($value, 0, $less-1);
				$used_filter = Rule::ERROR_LESS;
			}
		}else{
			// Фиксируем использованный фильтр
			$used_filter = Rule::ERROR_TYPE;
			$value = '';
		}
		return $value;
	}

	/**
	 * Проверка и фильтр текста (большая строка)
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return int|null
	 */
	static function Text($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		$rule->max(is_null($rule->getMax()) ? 65535 : min($rule->getMax(), 65535));
		$rule->min(is_null($rule->getMin()) ? 0 : max($rule->getMin(), 0));
		return self::String($value, $rule, $used_filter);
	}

	/**
	 * Текст размером до 4Мбайт (4194304 символов)
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return int|null
	 */
	static function BigText($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		$rule->max(is_null($rule->getMax()) ? 4194304 : min($rule->getMax(), 4194304));
		$rule->min(is_null($rule->getMin()) ? 0 : max($rule->getMin(), 0));
		return self::String($value, $rule, $used_filter);
	}

	/**
	 * Проверка и фильтр системного имени
	 * Символы: 0-9A-Z_a-z (без тире, начинается не с цифр)
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return string
	 */
	static function SysName($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		$value = self::String($value, $rule, $used_filter);
		// Проверка формата строки
		if (isset($value) && !$used_filter){
			$chars = '\x30-\x39\x41-\x5A\x5F\x61-\x7A';
			if ($more = $rule->getArgs('more-chars')){
				$chars.=$more;
			}
			$v = ltrim(preg_replace('/[^'.$chars.']/u', '', $value), '0123456789');
			if($v != $value){
				$used_filter = Rule::ERROR_TYPE;
				$value = $v;
			}
		}
    	return $value;
	}

	/**
	 * Проверка регулярным выражением
	 * @param $value Значение для проверки и фильтра
	 * @param \Engine\Rule $rule Правило проверки и фильтра
	 * @param bool $used_filter Примененый фильтр. Возвращаемое значение
	 * @return null|string
	 */
	static function RegExp($value, $rule = null, &$used_filter = false){
		if (!isset($rule)) $rule = self::GetDefaultRule();
		if ($rule->getCanEmpty() && (is_null($value) || $value=='')){
			return null;
		}
		$value = self::String($value, $rule, $used_filter);
		if (isset($value) && !$used_filter){
			if (!preg_match($rule->getArgs('pattern'), $value)){
				$used_filter = Rule::ERROR_TYPE;
				$value = null;
			}
		}
		return $value;
	}
}
