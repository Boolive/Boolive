<?php
/**
 * Правило для проверки и фильтра значений.
 * Правило указывает, каким должно быть значение. Проверку и фильтр выполняет класс \Engine\Check
 *
 * @link http://boolive.ru/createcms/rules-for-filter
 * @version 3.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Engine;
/**
 * Стандартные фильтры к правилу.
 * Методы создания правила с указанием первого фильтра.
 * @method static \Engine\Rule bool() Булево: false, 'false', 'off', 'no', '', '0' => false, иначе true
 * @method static \Engine\Rule int() Целое число в диапазоне от -2147483648 до 2147483647
 * @method static \Engine\Rule double() Действительное число в диапазоне от -1.7976931348623157E+308 до 1.7976931348623157E+308
 * @method static \Engine\Rule string() Строка любой длины
 * @method static \Engine\Rule null() Неопределенное значение. При этом проверяемый элемент должен существовать!
 * @method static \Engine\Rule arrays() Массив
 * @method static \Engine\Rule object() Объект указываемого класса
 * @method static \Engine\Rule entity() Объект класса \Engine\Entity или URI объекта, который можно получить из БД
 * @method static \Engine\Rule values() Объект класса \Engine\Values
 * @method static \Engine\Rule any() Любое правило из перечисленных или любой тип значения, если не перечислены варианты правил
 * @method static \Engine\Rule forbidden() Запрещенный. Требуется отсутствие элемента
 * @method static \Engine\Rule is() Равен указанному значению
 * @method static \Engine\Rule not() Не равен указанному значению
 * @method static \Engine\Rule in() Допустимые значения. Через запятую или массив
 * @method static \Engine\Rule not_in() Недопустимые значения. Через запятую или массив
 * @method static \Engine\Rule escape() Экранирование html символов
 * @method static \Engine\Rule striptags() Вырезание html тегов
 * @method static \Engine\Rule email() Email адрес
 * @method static \Engine\Rule url() URL
 * @method static \Engine\Rule uri() URI = URL + URN, возможно отсутсвие части URL или URN
 * @method static \Engine\Rule ip() IP
 * @method static \Engine\Rule regexp() Проверка на совпадение одному из регулярных выражений. Выражения через запятую или массив
 * @method static \Engine\Rule ospatterns() Проверка на совпадения одному из паттернов в стиле оболочки операционной системы: "*gr[ae]y". Паттерны запятую или массив
 * @method static \Engine\Rule color() HEX формат числа. Код цвета #FFFFFF. Возможны сокращения и опущение #
 *
 * Методы добавления фильтра к объекту правила.
 * @method \Engine\Rule max($max) Максимальное значение. Правая граница отрезка
 * @method \Engine\Rule min($min) Минимальное значение. Левая граница отрезка
 * @method \Engine\Rule less($less) Меньше указанного значения. Правая граница интервала
 * @method \Engine\Rule more($more) Больше указанного значения. Левая граница интервала
 * @method \Engine\Rule is() Равен указанному значению
 * @method \Engine\Rule not() Не равен указанному значению
 * @method \Engine\Rule in() Допустимые значения. Через запятую или массив
 * @method \Engine\Rule not_in() Недопустимые значения. Через запятую или массив
 * @method \Engine\Rule required() Должен существовать
 * @method \Engine\Rule default($value) Значение по умолчанию, если есть ошибки. Ошибка удаляется
 * @method \Engine\Rule ignore() Коды игнорируемых ошибок
 * @method \Engine\Rule trim() Обрезание строки
 * @method \Engine\Rule escape() Экранирование html символов
 * @method \Engine\Rule striptags() Вырезание html тегов
 * @method \Engine\Rule email() Email адрес
 * @method \Engine\Rule url() URL
 * @method \Engine\Rule uri() URI = URL + URN, возможно отсутсвие части URL или URN
 * @method \Engine\Rule ip() IP
 * @method \Engine\Rule regexp() Проверка на совпадение одному из регулярных выражений. Выражения через запятую или массив
 * @method \Engine\Rule ospatterns() Проверка на совпадения одному из паттернов в стиле оболочки операционной системы: "*gr[ae]y". Паттерны запятую или массив
 * @method \Engine\Rule color() HEX формат числа. Код цвета #FFFFFF. Возможны сокращения и опущение #
 */
class Rule implements ITrace{
	/** @var array Фильтры */
	private $filters = array();

	/**
	 * Создание правила.
	 * Создаётся и возвращается объект правила с добавленным первым фильтром, название которого соответсвует
	 * названию вызванного метода. Аргументы правила являются аргументами вызыванного метода.
	 * @static
	 * @example Rule::int();
	 * @param string $method Название фильтра (метода)
	 * @param $args Аргументы фильтра (метода)
	 * @return \Engine\Rule Новый объект правила
	 */
	static function __callStatic($method, $args){
		$rule = new Rule();
		$rule->filters[$method] = $args;
		return $rule;
	}

	/**
	 * Установка фильтра
	 * Если фильтр уже установлен, то он будет заменен новым
	 * @example Rule::int()->max(10)->filter2($arg);
	 * @param $name Имя фильтра
	 * @param $args Аргументы фильтра
	 * @return \Engine\Rule
	 */
	public function __call($name, $args){
		$this->filters[$name] = $args;
		return $this;
	}

	/**
	 * Выбор фильтра по имени
	 * @example $f = $rule->int->max;
	 * @param $name Название фильтра
	 * @return array Аргументы фильтра
	 */
	public function &__get($name){
		return $this->filters[$name];
	}

	/**
	 * Установка фильтра через присвоение
	 * @example $rule->max = 10; //установка фильтра max с аргументом 10
	 * @param $name Название фильтра
	 * @param mixed $args Массив аргументов. Если не является массивом, то значение будет помещено в массив
	 */
	public function __set($name, $args){
		if (!is_array($args)) $args = array($args);
		$this->filters[$name] = $args;
	}

	/**
	 * Проверка существования фильтра
	 * @example $is_exist = isset($rule->max);
	 * @param $name Название фильтра
	 * @return bool
	 */
	public function __isset($name){
		return isset($this->filters[$name]);
	}

	/**
	 * Удаление фильтра
	 * @example unset($rule->max);
	 * @param $name Название фильтра
	 */
	public function __unset($name){
		unset($this->filters[$name]);
	}

	/**
	 * Выбор всех фильтров
	 * @return array Ассоциативный массив фильтров, где ключ элемента - название фильтра, а значение - аргументы фильтра
	 */
	public function getFilters(){
		return $this->filters;
	}

	public function trace(){
		return $this->filters;
	}
}