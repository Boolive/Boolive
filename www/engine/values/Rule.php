<?php
/**
 * Правила для проверки и фильтра значений.
 * Правило указывает, каким должно быть значение. Саму проверку и фильтр выполняют классы Check и Values
 *
 * == Типы ==
 * - Правило основывается на указании типа желаемого значения. Типы соответсвуют поддерживаемым типам PHP.
 * - Есть возможность указывать варианты правил с помощью Rule::Any()
 *   или необходимость отсутствия элемента с помощью Rule::Forbidden().
 * @examle $rule = Rule::String();
 *
 * == Фильтры ==
 * - Дополнительно к типу добавляются фильтры, определющие формат значения после приведения его к нужному типу.
 * - Примерами фильтров являются ограничение максимальным и минимальным значением, соответсвиие строки шаблону,
 *   например email, указание списка допустимых или запрещенных значений, опредление значений по умолчанию и другие.
 * - В правиле может быть опредлено любое количество разных фильтров. Порядок указания фильтров опредляет
 *   последовательность выполнения фильтров. Исключением являются фильтры required, default и ignore, которые
 *   обрабатываются особым образом.
 * @examle $rule = Rule::String()->trim()->max(255);
 *
 * Можно добавлять и использовать свои фильтры, для этого достаточно:
 * 1) Придумать название фильтру, например myFilter.
 * 2) Создать статический метод фильтра в своём модуле, этот метод принимает аргументы в виде массива, фильтруемое
 *    значение и возвращаемую переменную под объект ошибки. Метод возвращает отфильтрованное значение и создает
 *    объект ошибки класса \Engine\Error, если значение было отфильтрвано.
 *    @examle
 *    static function myFilter($arg, $value, &$error = null){
 *       if ($value > $arg){
 * 			$value = $arg;
 *          $error = new Error('myFilter');
 * 		 }
 *       return $value;
 *    }
 * 3) Зарегистрировать метод фильтра на событие "Check::Filter_{name}", где {name} - навзание фильтра.
 *    Для регистрации события используется Events::AddHandler("Check::Filter_myFilter", "MyClass", "myFilter", true);
 * 4) Свой фильтр используется как все остальные - вызовом его у объекта правила, например:
 *    @examle Rule::String()->myFilter(99);
 *
 * @version 2.0
 * @link
 * @author Vladimir Shestakov <boolive@yandex.ru>
 * @examle
 * // Значение помещается в контейнер
 * $values = new Values(array('item'=>500));
 * // Получение целого числа не больше 400
 * echo $values->get('item', Rule::Int()->max(400));
 *
 * // Многоуровневое правило на массив значений
 * $Rule = Rule::ArrayList(array(
 *		'id'	=> Rule::Int()->required(),
 *		'name'	=> Rule::String()->max(64)->more(0),
 *  	'sub' => Rule::ArrayList(array(
 *			'value1'	=> Rule::Int()->default(0)
 *			'value2'	=> Rule::String()->max(10)
 *	 	)->required()
 * ));
 */
namespace Engine;

use ArrayAccess,
	Engine\Events;

/**
 * Стандартные фильры к правилу
 * @method \Engine\Rule max($max) Максимальное значение. Правая граница отрезка
 * @method \Engine\Rule min($min) Минимальное значение. Левая граница отрезка
 * @method \Engine\Rule less($less) Меньше указанного значения. Правая граница интервала
 * @method \Engine\Rule more($more) Больше указанного значения. Левая граница интервала
 * @method \Engine\Rule in() Допустимые значения. Через запятую или массив
 * @method \Engine\Rule not_in() Недопустимые значения. Через запятую или массив
 * @method \Engine\Rule required() Должен существовать
 * @method \Engine\Rule default($value) Значение по умолчанию, если есть ошибки. Ошибка удаляется
 * @method \Engine\Rule ignore() Коды игнорируемых ошибок
 * @method \Engine\Rule trim() Обрезание строки
 * @method \Engine\Rule escape() Экранирование html символов
 * @method \Engine\Rule email() Email адрес
 * @method \Engine\Rule url() URL
 * @method \Engine\Rule uri() URI = URL + URN
 * @method \Engine\Rule ip() IP
 * @method \Engine\Rule regexp() Проверка на совпадение одному из регулярных выражений. Выражения через запятую или массив
 * @method \Engine\Rule filenames() Проверка на совпадения одному из паттернов в стиле оболочки операционной системы: "*gr[ae]y". Паттерны запятую или массив
 * @method \Engine\Rule color() HEX формат числа. Код цвета #FFFFFF. Возможны сокращения и опущение #
 */
class Rule implements ArrayAccess{
	/** Названия типов правил, определяющие тип возвращаемого значения */
	/** Булево: false, 'false', 'off', 'no', '', '0' => false, иначе true */
	const TYPE_BOOL		= 'bool';
	/** Целое число */
	const TYPE_INT		= 'int';
	/** Число с плавающей точкой */
	const TYPE_DOUBLE	= 'double';
	/** Строка любой длины */
	const TYPE_STRING	= 'string';
	/** Неопределенное значение. При этом проверяемый элемент должен существовать! */
	const TYPE_NULL		= 'null';
	/** Массив */
	const TYPE_ARRAY	= 'array';
	/** Объект указываемого класса */
	const TYPE_OBJECT 	= 'object';
	/** Объект класса \Engine\Entity или URI объекта, который можно получить */
	const TYPE_ENTITY	= 'entity';
	/** Объект класса \Engine\Values */
	const TYPE_VALUES	= 'values';
	/** Любое правило из перечисленных или любой тип значения, если не перечислены варианты правил */
	const TYPE_ANY 		= 'any';
	/** Запрещенный. Требуется отсутствие элемента */
	const TYPE_FORBIDDEN = 'forbidden';

	/** @var string Тип правила в соответсвии с константами Rule::TYPE_* */
	private $type;
	/** @var array Массив вложенных правил на элементы массива или как варианты правил */
	private $rule_sub = array();
	/** @var \Engine\Rule|null Правило по умолчанию на элементы массива.  */
	private $rule_default;
	/**
	 * Признак рекурсиовной обработки массивов.
	 * Обрабатывать (true) или нет (false) вложенные массивы этим же правилом,
	 * если отсутствует или не подходит правило по умолчанию?
	 * @var bool
	 */
	private $recursive = false;
	/** @var string Имя класс для правила на объект */
	private $class;
	/** @var array Фильтры */
	private $filters = array();

	/**
	 * @param string $type Тип правила
	 */
	private function __construct($type){
		$this->type = $type;
	}
	#########################################
	#										#
	#		Методы создания правила			#
	#										#
	#########################################
	/**
	 * Булево: false, 'false', 'off', 'no', '', '0' => false. Иначе true
	 * @return \Engine\Rule
	 */
	static function Bool(){
		return new Rule(Rule::TYPE_BOOL);
	}

	/**
	 * Целое число
	 * @return \Engine\Rule
	 */
	static function Int(){
		return new Rule(Rule::TYPE_INT);
	}

	/**
	 * Число с плавающей точкой: -1.7976931348623157E+308 .. 1.7976931348623157E+308 (64бит)
	 * @return \Engine\Rule
	 */
	static function Double(){
		return new Rule(Rule::TYPE_DOUBLE);
	}

	/**
	 * Строка любой длины
	 * @return \Engine\Rule
	 */
	static function String(){
		return new Rule(Rule::TYPE_STRING);
	}

	/**
	 * Неопределенное значение
	 * При этом проверяемый элемент должен существовать!
	 * @return \Engine\Rule
	 */
	static function Null(){
		return new Rule(Rule::TYPE_NULL);
	}

	/**
	 * Одномерный массив
	 * Если не указаны правила на элементы, то применяется общее правило для выборки всех элементов
	 * @param array $sub_rule Ассоциативный массив правил на элементы массива
	 * @param null $default_rule
	 * @return \Engine\Rule
	 */
	static function ArrayList($sub_rule = array(), $default_rule = null){
		$rule = new Rule(Rule::TYPE_ARRAY);
		if (is_array($sub_rule)){
			$rule->rule_sub = $sub_rule;
		}else
		if (is_null($default_rule)){
			$default_rule = $sub_rule;
		}
		if (is_null($default_rule) || $default_rule instanceof Rule) $rule->rule_default = $default_rule;
		return $rule;
	}

	/**
	 * Многомерный массив
	 * Для выборки всех значений с рекурсивной проверкой
	 * @param null $default_rule
	 * @return \Engine\Rule
	 */
	static function ArrayTree($default_rule = null){
		$rule = new Rule(Rule::TYPE_ARRAY);
		$rule->recursive = true;
		if (is_null($default_rule) || $default_rule instanceof Rule) $rule->rule_default = $default_rule;
		return $rule;
	}

	/**
	 * Объект указанного класса
	 * @param $class Имя класс с учетом пространства имен
	 * @return \Engine\Rule
	 */
	static function Object($class = null){
		$rule = new Rule(Rule::TYPE_OBJECT);
		$rule->class = $class;
		return $rule;
	}

	/**
	 * Объект класса \Engine\Entity или его наследников
	 * Если значение является строкой и представляет uri, то будет попытка получить объект из секции
	 * @param $class Имя класс с учетом пространства имен.
	 * @return \Engine\Rule
	 */
	static function Entity($class = null){
		$rule = new Rule(Rule::TYPE_ENTITY);
		if (empty($class)) $class = 'Engine\\Entity';
		$rule->class = $class;
		return $rule;
	}

	/**
	 * Объект класса \Engine\Values
	 * @return \Engine\Rule
	 */
	static function Values(){
		return new Rule(Rule::TYPE_VALUES);
	}

	/**
	 * Любое правило из перечисленных
	 * @return \Engine\Rule
	 */
	static function Any(){
		$rule = new Rule(Rule::TYPE_ANY);
		$arg = func_get_args();
		if (isset($arg[0]) && is_array($arg[0])) $arg = $arg[0];
		if (is_array($arg)){
			$rule->rule_sub = $arg;
		}
		return $rule;
	}

	/**
	 * Запрещенный. Требуется отсутствие элемента
	 * @return \Engine\Rule
	 */
	static function Forbidden(){
		return new Rule(Rule::TYPE_FORBIDDEN);
	}

	#################################################
	#												#
	#		Фильтры к правилу						#
	#												#
	#################################################
	/**
	 * Установка фильтра к правилу
	 * Если фильтр уже установлен, то он будет заменен новым
	 * @example Rule::Int()->max(10)->filter2($arg);
	 * @param $name Имя фильтра
	 * @param $args Аргументы фильтра
	 * @return \Engine\Rule
	 */
	public function __call($name, $args){
		if (sizeof($args)>1) $args = array($args);
		$this->filters[$name] = $args;
		return $this;
	}

	/**
	 * Выбор фильтров
	 * @return array Ассоциативный массив фильтров, где ключ элемента - название фильтра, а значение - аргументы фильтра
	 */
	public function getFilters(){
		return $this->filters;
	}

	/**
	 * Выбор фильтра по имени
	 * @param $name
	 * @return null
	 */
	public function getFilter($name){
		return isset($this->filters[$name]) ? $this->filters[$name] : null;
	}

	/**
	 * Проверка существования фильтра
	 * @param $name Название фильтра
	 * @return bool
	 */
	public function isFilterExist($name){
		return isset($this->filters[$name]);
	}

	#################################################
	#												#
	#		Свойства правила						#
	#												#
	#################################################
	/**
	 * Тип правила в соответствии с константами Rule::TYPE_*
	 * @return string
	 */
	public function getType(){
		return $this->type;
	}

	/**
	 * Правило по умолчанию для элементов массива
	 * @return \Engine\Rule|null
	 */
	public function getRuleDefault(){
		return $this->rule_default;
	}

	/**
	 * Установка правила по умолчанию для элементов массива
	 * @param \Engine\Rule|null $rule
	 */
	public function setRuleDefault($rule){
		if (is_null($rule) || $rule instanceof Rule){
			$this->rule_default = $rule;
		}
	}

	/**
	 * Класс проверяемого объекта
	 * @return string|null
	 */
	public function getClass(){
		return $this->class;
	}

	/**
	 * Установка класса для проверки объекта
	 * @param string $class Имя класс с учетом пространства имен
	 */
	public function setClass($class){
		$this->class = $class;
	}

	/**
	 * Вложенные правила
	 * В зависимости от типа используются либо для указания правил на элементы массива, либо как варианты правил
	 * @return array
	 */
	public function getRuleSub(){
		return $this->rule_sub;
	}

	/**
	 * Установка вложенных правил
	 * @param array $rules
	 */
	public function setRuleSub($rules = array()){
		if (is_array($rules)){
			$this->rule_sub = $rules;
		}
	}

	/**
	 * Признак рекурсиовной обработки массивов.
	 * Обрабатывать (true) или нет (false) вложенные массивы этим же правилом,
	 * если отсутствует или не подходит правило по умолчанию?
	 * @return bool
	 */
	public function getRecursive(){
		return $this->recursive;
	}

	/**
	 * Устанеовка признака рекурсиовной обработки массивов.
	 * Обрабатывать (true) или нет (false) вложенные массивы этим же правилом,
	 * если отсутствует или не подходит правило по умолчанию?
	 * @param $recursive
	 */
	public function setRecursive($recursive){
		$this->recursive = $recursive;
	}

	/**
	 * Проверка существования вложенного правила по ключу
	 * @example isset($rule['sub']);
	 * @param string|int $offset Ключ правила
	 * @return bool
	 */
	public function offsetExists($offset){
		return isset($this->rule_sub[$offset]);
	}

	/**
	 * Получение вложенного правила по ключу
	 * @example $sub = $rule['sub1'];
	 * @param string|int $offset Ключ правила
	 * @return mixed|null
	 */
	public function offsetGet($offset){
		if (isset($this->rule_sub[$offset])){
			return $this->rule_sub[$offset];
		}else{
			return null;
		}
	}

	/**
	 * Установка вложенного правила по ключу
	 * @example $rule['sub2'] = Rule::String();
	 * @param string|int|null $offset Ключ правила
	 * @param \Engine\Rule $rule
	 */
	public function offsetSet ($offset, $rule){
		if (!isset($this->rule_sub)) $this->rule_sub = array();
		if ($rule instanceof Rule){
			if (is_null($offset)){
				$this->rule_sub[] = $rule;
			}else{
				$this->rule_sub[$offset] = $rule;
			}
		}
	}

	/**
	 * Удаление вложенного правила по ключу
	 * @example unsset($rule['sub']);
	 * @param string|int $offset Ключ правила
	 */
	public function offsetUnset($offset){
		if (isset($this->rule_sub[$offset])) unset($this->rule_sub[$offset]);
	}
}