<?php
/**
 * Правило для проверки и фильтра значений.
 * Правило указывает, каким должно быть значение. Проверку и фильтр выполняет класс \Boolive\values\Check
 *
 * @link http://boolive.ru/createcms/rules-for-filter
 * @version 3.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */

namespace Boolive\values;

use Boolive\develop\ITrace;

/**
 * Стандартные фильтры к правилу.
 * Методы создания правила с указанием первого фильтра.
 * @method static \Boolive\values\Rule bool() Булево: false, 'false', 'off', 'no', '', '0' => false, иначе true
 * @method static \Boolive\values\Rule int() Целое число в диапазоне от -2147483648 до 2147483647
 * @method static \Boolive\values\Rule double() Действительное число в диапазоне от -1.7976931348623157E+308 до 1.7976931348623157E+308
 * @method static \Boolive\values\Rule string() Строка любой длины
 * @method static \Boolive\values\Rule null() Неопределенное значение. При этом проверяемый элемент должен существовать!
 * @method static \Boolive\values\Rule arrays() Массив
 * @method static \Boolive\values\Rule object() Объект указываемого класса
 * @method static \Boolive\values\Rule entity() Объект класса \Boolive\data\Entity или URI объекта, который можно получить из БД. В аргументе фильтра указывается услвоие на объект в виде массива.
 * @method static \Boolive\values\Rule values() Объект класса \Boolive\values\Values
 * @method static \Boolive\values\Rule any() Любое правило из перечисленных или любой тип значения, если не перечислены варианты правил
 * @method static \Boolive\values\Rule forbidden() Запрещенный. Требуется отсутствие элемента
 * @method static \Boolive\values\Rule eq() Равен указанному значению
 * @method static \Boolive\values\Rule not() Не равен указанному значению
 * @method static \Boolive\values\Rule in() Допустимые значения. Через запятую или массив
 * @method static \Boolive\values\Rule not_in() Недопустимые значения. Через запятую или массив
 * @method static \Boolive\values\Rule escape() Экранирование html символов
 * @method static \Boolive\values\Rule striptags() Вырезание html тегов
 * @method static \Boolive\values\Rule email() Email адрес
 * @method static \Boolive\values\Rule url() URL
 * @method static \Boolive\values\Rule uri() URI = URL + URN, возможно отсутсвие части URL или URN
 * @method static \Boolive\values\Rule ip() IP
 * @method static \Boolive\values\Rule regexp() Проверка на совпадение одному из регулярных выражений. Выражения через запятую или массив
 * @method static \Boolive\values\Rule ospatterns() Проверка на совпадения одному из паттернов в стиле оболочки операционной системы: "*gr[ae]y". Паттерны запятую или массив
 * @method static \Boolive\values\Rule color() HEX формат числа. Код цвета #FFFFFF. Возможны сокращения и опущение #
 * @method static \Boolive\values\Rule lowercase() Преобразует строку в нижний регистр
 * @method static \Boolive\values\Rule uppercase() Преобразует строку в верхний регистр
 *
 * Методы добавления фильтра к объекту правила.
 * @method \Boolive\values\Rule max($max) Максимальное значение. Правая граница отрезка
 * @method \Boolive\values\Rule min($min) Минимальное значение. Левая граница отрезка
 * @method \Boolive\values\Rule less($less) Меньше указанного значения. Правая граница интервала
 * @method \Boolive\values\Rule more($more) Больше указанного значения. Левая граница интервала
 * @method \Boolive\values\Rule eq() Равен указанному значению
 * @method \Boolive\values\Rule not() Не равен указанному значению
 * @method \Boolive\values\Rule in() Допустимые значения. Через запятую или массив
 * @method \Boolive\values\Rule not_in() Недопустимые значения. Через запятую или массив
 * @method \Boolive\values\Rule required() Должен существовать
 * @method \Boolive\values\Rule default($value) Значение по умолчанию, если есть ошибки. Ошибка удаляется
 * @method \Boolive\values\Rule ignore() Коды игнорируемых ошибок
 * @method \Boolive\values\Rule trim() Обрезание строки
 * @method \Boolive\values\Rule escape() Экранирование html символов
 * @method \Boolive\values\Rule striptags() Вырезание html тегов
 * @method \Boolive\values\Rule email() Email адрес
 * @method \Boolive\values\Rule url() URL
 * @method \Boolive\values\Rule uri() URI = URL + URN, возможно отсутсвие части URL или URN
 * @method \Boolive\values\Rule ip() IP
 * @method \Boolive\values\Rule regexp() Проверка на совпадение одному из регулярных выражений. Выражения через запятую или массив
 * @method \Boolive\values\Rule ospatterns() Проверка на совпадения одному из паттернов в стиле оболочки операционной системы: "*gr[ae]y". Паттерны запятую или массив
 * @method \Boolive\values\Rule color() HEX формат числа. Код цвета #FFFFFF. Возможны сокращения и опущение #
 * @method \Boolive\values\Rule file_upload() Информация о загружаемом файле в виде массива
 * @method \Boolive\values\Rule lowercase() Преобразует строку в нижний регистр
 * @method \Boolive\values\Rule uppercase() Преобразует строку в верхний регистр
 */
class Rule implements ITrace
{
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
     * @return \Boolive\values\Rule Новый объект правила
     */
    static function __callStatic($method, $args)
    {
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
     * @return \Boolive\values\Rule
     */
    public function __call($name, $args)
    {
        $this->filters[$name] = $args;
        return $this;
    }

    /**
     * Выбор фильтра по имени
     * @example $f = $rule->int->max;
     * @param $name Название фильтра
     * @return array Аргументы фильтра
     */
    public function &__get($name)
    {
        return $this->filters[$name];
    }

    /**
     * Установка фильтра через присвоение
     * @example $rule->max = 10; //установка фильтра max с аргументом 10
     * @param $name Название фильтра
     * @param mixed $args Массив аргументов. Если не является массивом, то значение будет помещено в массив
     */
    public function __set($name, $args)
    {
        if (!is_array($args)) $args = array($args);
        $this->filters[$name] = $args;
    }

    /**
     * Проверка существования фильтра
     * @example $is_exist = isset($rule->max);
     * @param $name Название фильтра
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->filters[$name]);
    }

    /**
     * Удаление фильтра
     * @example unset($rule->max);
     * @param $name Название фильтра
     */
    public function __unset($name)
    {
        unset($this->filters[$name]);
    }

    /**
     * Выбор всех фильтров
     * @return array Ассоциативный массив фильтров, где ключ элемента - название фильтра, а значение - аргументы фильтра
     */
    public function getFilters()
    {
        return $this->filters;
    }

    public function trace()
    {
        return $this->filters;
    }
}