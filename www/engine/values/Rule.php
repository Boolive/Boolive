<?php
/**
 * Правила для проверки и фильтра значений
 *
 * @version 1.0
 * @link http://boolive.ru/createcms/filter-and-check-data
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @examle
 * // Простое правило
 * $rule = Rule::Email();
 *
 * // Многоуровневое правило на массив значений
 * $rule = Rule::ArrayList(array(
 *		'id'	=> Rule::UInt()->exist(false),
 *		'name'	=> Rule::String()->max(64)->more(0),
 *  	'sub' => Rule::ArrayList(array(
 *			'value1'	=> Rule::Int(8)->set_default(0)
 *			'value2'	=> Rule::String()->max(10)
 *	 	)->exist(false)
 * ));
 *
 * Для создания своего типа достаточно:
 * 1) придумать новое название типа;
 * 2) создать метод проверки значения на соответствие типу. Примеры в \Engine\Check;
 * 3) зарегистрировать метод проверки на событие "Check::Filter_{type}", где {type} - навзание типа.
 * Дополнительные свойства проверки передаются через аргументы правила.
 * Создание экземпляра правила со своим типов выполняется вызовом метода соответсвующего названию правила:
 * $rule = Rule::MyType();
 */
namespace Engine;

use Engine\Events;

class Rule {
	/** Названия предопредленных типов для фильтра значений */
	/** Булево: false, 'false', 'off', 'no', '', '0' => false. Иначе true */
	const TYPE_BOOL		= 'bool';
	/** Целое число: -128 .. 127 (8бит)*/
	const TYPE_INT8		= 'int8';
	/** Целое число без знака: 0 .. 255 (8бит)*/
	const TYPE_UINT8	= 'uint8';
	/** Целое число: -2147483648 .. 2147483647 (32бит)*/
	const TYPE_INT32	= 'int32';
	/** Целое число без знака: 0 .. 4294967295 (32бит)*/
	const TYPE_UINT32	= 'uint32';
	/** Целое число: -9223372036854770000 .. 9223372036854770000 (64бит)*/
	const TYPE_INT64	= 'int64';
	/** Целое число без знака: 0 .. 18446744073709500000 (64бит)*/
	const TYPE_UINT64	= 'uint64';
	/** Число с плавающей точкой: -1.7976931348623157E+308 .. 1.7976931348623157E+308 (64бит)*/
	const TYPE_DOUBLE	= 'double';
	/** Строка: 255 байт  */
	const TYPE_STRING	= 'string';
	/** Строка: 65535 байт */
	const TYPE_TEXT		= 'text';
	/** Строка: 4194304 байт */
	const TYPE_BIGTEXT	= 'bigtext';
	/** Строка: [0-9A-Z_a-z] (без тире, начинается не с цифр, длиной до 255 байт) */
	const TYPE_SYSNAME	= 'sysname';
	/** Е-mail адрес */
	const TYPE_EMAIL	= 'email';
	/** Код цвета #000000..#FFFFFF Допустимо без # */
	const TYPE_COLOR	= 'color';
	/** Объект Values */
	const TYPE_VALUES	= 'values';
	/** Любой тип (без фильтра) */
	const TYPE_NO		= 'no';
	/** Отсутствие значения */
	const TYPE_NULL		= 'null';
	/** Объект класса \Engine\Entity */
	const TYPE_ENTITY	= 'entity';
	/** Отсутствие или пустое (null, '') значение */
	const TYPE_EMPTY 	= 'empty';
	/** Регулярное выражение */
	const TYPE_REG_EXP = 'regexp';
	/** Объект */
	const TYPE_OBJECT = 'object';

	/** Ошибки после фильтров */
	/** Неизвестный тип */
	const ERROR_UNKNOWN_TYPE = 'UNKNOWN_TYPE';
	/** Проверка соответсвия типу */
	const ERROR_TYPE	= 'TYPE';
	/** Проверка минимально допустимого значения */
	const ERROR_MIN		= 'MIN';
	/** Проверка максимально допустимого значения */
	const ERROR_MAX		= 'MAX';
	/** Проверка вхождения в список значений */
	const ERROR_IN		= 'IN';
	/** Проверка отсутствия в списке */
	const ERROR_NOT_IN	= 'NOT_IN';
	/** Проверка, что значене меньше указанного  */
	const ERROR_LESS 	= 'LESS';
	/** Проверка, что значение больше указанного */
	const ERROR_MORE 	= 'MORE';
	/** Проверка отсутствия значения */
	const ERROR_NULL	= 'NULL';
	/** Проверка сущестования значения */
	const ERROR_EXIST	= 'EXIST';
	/** Проверка сущестования значения и его не пустоты */
	const ERROR_EMPTY	= 'EMPTY';
	/** Не существует */
	//const ERROR_NOT_EXIST	 = 'NOT_EXIST';
	/** Сущесвтует */
	//const ERROR_NOT_EXIST = 'NOT_EXIST';
	/** Нет правила */
	const ERROR_NO_RULE = 'NO_RULE';

	/** Виды обработки данных */
	/** Одиночное значение */
	const KIND_SINGLE 	 = 0;
	/** Обработка всех значений одного уровня вложености (одномерного массива) */
	const KIND_ARRAY 	 = 1;
	/** Рекурсивный обработка всех значений (для многомерныго массива)  */
	const KIND_ARRAY_REC = 2;

	/** Свойства правила */
	/** @var int Вид правила (одиночное, список) */
	private $kind = Rule::KIND_SINGLE;
	/** @var string Тип правила (тип данных, формат) */
	private $type = Rule::TYPE_STRING;
	/** @var Минимальное значение */
	private $min;
	/** @var Максимальное значение */
	private $max;
	/** @var Больше указанного значения */
	private $more;
	/** @var Меньше указанного значения */
	private $less;
	/** @var bool Признак обязательного наличия */
	private $exist = true;
	/** @var array Список игнорируемых результатов проверки. Остальные добавляются в объект исключений */
	private $ignore = array();
	/** @var Значение по умолчанию, если проверяемое значение не определено, но обязательно */
	private $default;
	/** @var bool Признак установленного значения по умолчанию */
	private $is_set_default = false;
	/** @var array Массив допустимых значений */
	private $in = array();
	/** @var array Массив запрещенных значений */
	private $not_in = array();
	/** @var array Дополнительные аргументы проверки */
	private $args = array();
	/** @var bool Признак, возвращать ассоциативный массив значений или целочисленный */
	private $assoc = true;
	/** @var bool Признак, может ли значение быть пустым */
	private $can_empty = false;
	/** @var array|Rule Правило на вложенный массив */
	private $sub_rule;
	/** @var string Название класса для правила на объект */
	private $class = '';

	private function __construct($kind = Rule::KIND_SINGLE, $type = Rule::TYPE_STRING){
		$this->kind = $kind;
		$this->type = $type;
	}
	#########################################
	#										#
	#		Методы создания правила			#
	#										#
	#########################################
	/**
	 * Одномерный массив
	 * Если не указаны правила на элементы, то применяется общее правило для выборки всех элементов
	 * @param array $sub_rule Ассоциативный массив правил на элементы массива
	 * @return Rule
	 */
	static function ArrayList($sub_rule = array()){
		$rule = new Rule(Rule::KIND_ARRAY, Rule::TYPE_NO);
		$rule->sub_rule = $sub_rule;
		return $rule;
	}

	/**
	 * Многомерный массив
	 * Для выборки всех значений с рекурсивной проверкой
	 * @return Rule
	 */
	static function ArrayTree(){
		return new Rule(Rule::KIND_ARRAY_REC, Rule::TYPE_NO);
	}

	/**
	 * Объект указанного класса
	 * @param $class Имя класс с учетом пространства имен
	 * @return Rule
	 */
	static function Object($class = null){
		$rule = new Rule(Rule::KIND_SINGLE, Rule::TYPE_OBJECT);
		$rule->class = $class;
		return $rule;
	}

	/**
	 * Объект класса \Engine\Entity или его наследников
	 * Если значение является массивом и имеет элементы section и id, то будет попытка превратить массив в соотвующий объект Entity
	 * @param $class Имя класс с учетом пространства имен.
	 * @return Rule
	 */
	static function Entity($class = null){
		$rule = new Rule(Rule::KIND_SINGLE, Rule::TYPE_ENTITY);
		if (empty($class)) $class = 'Engine\\Entity';
		$rule->class = $class;
		return $rule;
	}

	/**
	 * Булево: false, 'false', 'off', 'no', '', '0' => false. Иначе true
	 * @return Rule
	 */
	static function Bool(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_BOOL);
	}

	/**
	 * Целое число
	 * @param int $bit Количество бит: 8, 32, 64
	 * @return Rule
	 */
	static function Int($bit = 32){
		switch ($bit){
			case 8:	 return new Rule(Rule::KIND_SINGLE, Rule::TYPE_INT8);
			case 64: return new Rule(Rule::KIND_SINGLE, Rule::TYPE_INT64);
			default: return new Rule(Rule::KIND_SINGLE, Rule::TYPE_INT32);
		}
	}

	/**
	 * Целое число без знака
	 * @param int $bit Количество бит: 8, 32, 64
	 * @return Rule
	 */
	static function UInt($bit = 32){
		switch ($bit){
			case 8:	 return new Rule(Rule::KIND_SINGLE, Rule::TYPE_UINT8);
			case 64: return new Rule(Rule::KIND_SINGLE, Rule::TYPE_UINT64);
			default: return new Rule(Rule::KIND_SINGLE, Rule::TYPE_UINT32);
		}
	}

	/**
	 * Число с плавающей точкой: -1.7976931348623157E+308 .. 1.7976931348623157E+308 (64бит)
	 * @return Rule
	 */
	static function Double(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_DOUBLE);
	}

	/**
	 * Строка: 255 байт
	 * @return Rule
	 */
	static function String(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_STRING);
	}

	/**
	 * Строка (текст): 65535 байт
	 * @return Rule
	 */
	static function Text(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_TEXT);
	}

	/**
	 * Строка (большой текст): 4194304 байт
	 * @return Rule
	 */
	static function BigText(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_BIGTEXT);
	}

	/**
	 * Строка для именования переменных: [0-9A-Z_a-z] (без тире, начинается не с цифр, длиной до 255 байт)
	 * @return Rule
	 */
	static function SysName(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_SYSNAME);
	}

	/**
	 * Е-mail адрес
	 * @return Rule
	 */
	static function Email(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_EMAIL);
	}

	/**
	 * Код цвета #000000..#FFFFFF Допустимо без #
	 * @return Rule
	 */
	static function Color(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_COLOR);
	}

	/**
	 * Объект Values
	 * @return Rule
	 */
	static function Values(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_VALUES);
	}

	/**
	 * Отсутствие значения
	 * @return Rule
	 */
	static function Null(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_NULL);
	}

	/**
	 * Любой тип (без фильтра)
	 * @return Rule
	 */
	static function No(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_NO);
	}

	/**
	 * Регулярное выражение
	 * @param $pattern Шаблон регулярного выражения
	 * @return Rule
	 */
	static function RexExp($pattern){
		$rule = new Rule(Rule::KIND_SINGLE, Rule::TYPE_REG_EXP);
		return $rule->arg('pattern', $pattern);
	}

	/**
	 * Отсутствие или пустое (null, '') значение
	 * @return Rule
	 */
	static function EmptyVal(){
		return new Rule(Rule::KIND_SINGLE, Rule::TYPE_EMPTY);
	}
	/**
	 * Создание пользователького типа
	 * @param $name Название типа
	 * @param $arguments
	 * @return Rule
	 */
	static function __callStatic($name, $arguments = null){
		return new Rule(Rule::KIND_SINGLE, $name);
	}


	#################################################
	#												#
	#		Методы установка свойства правил		#
	#												#
	#################################################
	/**
	 * Вид фильтра
	 * @param string $kind
	 * @return Rule
	 */
	public function kind($kind){
		$this->kind = (int)$kind;
		return $this;
	}

	/**
	 * Тип фильтра
	 * Обычно тип определяется в методах создания правила
	 * @param string $type
	 * @return Rule
	 */
	public function type($type = Rule::TYPE_STRING){
		$this->type = (string)$type;
		return $this;
	}

	/**
	 * Минимальное значение
	 * @param $value
	 * @return Rule
	 */
	public function min($value){
		$this->min = $value;
		return $this;
	}

	/**
	 * Максимальное значение
	 * @param $value
	 * @return Rule
	 */
	public function max($value){
		$this->max = $value;
		return $this;
	}

	/**
	 * Больше чем $value
	 * @param $value
	 * @return Rule
	 */
	public function more($value){
		$this->more = $value;
		return $this;
	}

	/**
	 * Меньше чем $value
	 * @param $value
	 * @return Rule
	 */
	public function less($value){
		$this->less = $value;
		return $this;
	}

	/**
	 * Признак обязательности существования
	 * @param $exist
	 * @return Rule
	 */
	public function exist($exist){
		$this->exist = (bool)$exist;
		return $this;
	}

	/**
	 * Какие ошибки игнорировать
	 * @param Строковые значения через запятую
	 * @return Rule
	 */
	public function ignore(){
		$this->ignore = func_get_args();
		if (isset($this->ignore[0]) && is_array($this->ignore[0])) $this->ignore = $this->ignore[0];
		return $this;
	}

	/**
	 * Значение по умолчанию, если значение отсутствует
	 * @param $value
	 * @return Rule
	 */
	public function set_default($value){
		$this->default = $value;
		$this->is_set_default = true;
		return $this;
	}

	/**
	 * Отмена значения по умолчанию
	 * @return Rule
	 */
	public function unset_default(){
		$this->is_set_default = false;
		return $this;
	}

	/**
	 * Дополнительные аргументы к применяемому типу фильтра
	 * @param string $name Название аргумента
	 * @param mixed $value Значение аргумента
	 * @return Rule
	 */
	public function arg($name, $value){
		$this->args[$name] = $value;
		return $this;
	}

	/**
	 * Список допустимых значений для фильтруемого
	 * @return Rule
	 */
	public function in(){
		$this->in = func_get_args();
		if (isset($this->in[0]) && is_array($this->in[0])) $this->in = $this->in[0];
		return $this;
	}

	/**
	 * Список НЕдопустимых значений
	 * @return Rule
	 */
	public function not_in(){
		$this->not_in = func_get_args();
		if (isset($this->not_in[0]) && is_array($this->not_in[0])) $this->not_in = $this->not_in[0];
		return $this;
	}

	/**
	 * Возвращать ассоциативный (true) массив или обычный
	 * Применяется для фильтров ArrayList и ArrayRecursive
	 * @param bool $assoc
	 * @return Rule
	 */
	public function assoc($assoc){
		$this->assoc = (bool)$assoc;
		return $this;
	}

	/**
	 * Может быть неопредленным
	 * @param $can_empty
	 * @return Rule
	 */
	public function can_empty($can_empty = true){
		$this->can_empty = (bool)$can_empty;
		return $this;
	}

	#################################################
	#												#
	#		Методы получение свойств правила		#
	#												#
	#################################################
	/**
	 * Вид правила (одиночное, список)
	 * @return int
	 */
	public function getKind(){
		return $this->kind;
	}

	/**
	 * Тип правила (тип данных, формат)
	 * @return string
	 */
	public function getType(){
		return $this->type;
	}

	/**
	 * Минимальное значение
	 * @return mixed
	 */
	public function getMin(){
		return $this->min;
	}

	/**
	 * Максимальное значение
	 * @return mixed
	 */
	public function getMax(){
		return $this->max;
	}

	/**
	 *  Больше данного значения
	 * @return mixed
	 */
	public function getMore(){
		return $this->more;
	}

	/**
	 * Меньше данного значения
	 * @return mixed
	 */
	public function getLess(){
		return $this->less;
	}

	/**
	 * Признак обязательного наличия
	 * @return bool
	 */
	public function getExist(){
		return $this->exist;
	}

	/**
	 * Список игнорируемых результатов проверки. Остальные добавляются в объект исключений
	 * @return array
	 */
	public function getIgnore(){
		return $this->ignore;
	}

	/**
	 * Значение по умолчанию, если проверяемое значение не определено, но обязательно
	 * @return mixed
	 */
	public function getDefault(){
		return $this->default;
	}

	/**
	 * Признак установленного значения по умолчанию
	 * @return bool
	 */
	public function isSetDefault(){
		return $this->is_set_default;
	}

	/**
	 * Дополнительный аргумент для метода проверки
	 * @param null $name Название параметра
	 * @return array|null
	 */
	public function getArgs($name = null){
		if (isset($name)){
			return isset($this->args[$name])?$this->args[$name]:null;
		}
		return $this->args;
	}

	/**
	 * Массив допустимых значений
	 * @return array
	 */
	public function getIn(){
		return $this->in;
	}

	/**
	 * Массив запрещенных значений
	 * @return array
	 */
	public function getNotIn(){
		return $this->not_in;
	}

	/**
	 * Признак, возвращать ассоциативный массив значений или целочисленный
	 * @return bool
	 */
	public function getAssoc(){
		return $this->assoc;
	}

	/**
	 * Признак, может ли значение быть пустым
	 * @return bool
	 */
	public function getCanEmpty(){
		return $this->can_empty;
	}

	/**
	 * Правило на вложенный массив
	 * @return array
	 */
	public function getSubRule(){
		return $this->sub_rule;
	}

	/**
	 * Название класса для правила на объект
	 * @return string
	 */
	public function getClass(){
		return $this->class;
	}
}
