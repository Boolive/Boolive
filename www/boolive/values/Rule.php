<?php
/**
 * Правило для проверки и фильтра значений.
 * Правило указывает, каким должно быть значение. Проверку и фильтр выполняет класс \boolive\values\Check
 *
 * @link http://boolive.ru/createcms/rules-for-filter
 * @version 3.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */

namespace boolive\values;

use boolive\develop\ITrace;

/**
 * Стандартные фильтры к правилу.
 * Методы создания правила с указанием первого фильтра.
 * @method static \boolive\values\Rule bool() Булево: false, 'false', 'off', 'no', '', '0' => false, иначе true
 * @method static \boolive\values\Rule int() Целое число в диапазоне от -2147483648 до 2147483647
 * @method static \boolive\values\Rule double() Действительное число в диапазоне от -1.7976931348623157E+308 до 1.7976931348623157E+308
 * @method static \boolive\values\Rule string() Строка любой длины
 * @method static \boolive\values\Rule scalar() Строка, число, булево
 * @method static \boolive\values\Rule null() Неопределенное значение. При этом проверяемый элемент должен существовать!
 * @method static \boolive\values\Rule arrays() Массив
 * @method static \boolive\values\Rule object() Объект указываемого класса
 * @method static \boolive\values\Rule entity() Объект класса \boolive\data\Entity или URI объекта, который можно получить из БД. В аргументе фильтра указывается условие на объект в виде массива.
 * @method static \boolive\values\Rule values() Объект класса \boolive\values\Values
 * @method static \boolive\values\Rule any() Любое правило из перечисленных или любой тип значения, если не перечислены варианты правил
 * @method static \boolive\values\Rule forbidden() Запрещенный. Требуется отсутствие элемента
 * @method static \boolive\values\Rule eq() Равен указанному значению
 * @method static \boolive\values\Rule not() Не равен указанному значению
 * @method static \boolive\values\Rule in() Допустимые значения. Через запятую или массив
 * @method static \boolive\values\Rule not_in() Недопустимые значения. Через запятую или массив
 * @method static \boolive\values\Rule escape() Экранирование html символов
 * @method static \boolive\values\Rule striptags() Вырезание html тегов
 * @method static \boolive\values\Rule email() Email адрес
 * @method static \boolive\values\Rule url() URL
 * @method static \boolive\values\Rule uri() URI = URL + URN, возможно отсутсвие части URL или URN
 * @method static \boolive\values\Rule ip() IP
 * @method static \boolive\values\Rule regexp() Проверка на совпадение одному из регулярных выражений. Выражения через запятую или массив
 * @method static \boolive\values\Rule ospatterns() Проверка на совпадения одному из паттернов в стиле оболочки операционной системы: "*gr[ae]y". Паттерны запятую или массив
 * @method static \boolive\values\Rule color() HEX формат числа. Код цвета #FFFFFF. Возможны сокращения и опущение #
 * @method static \boolive\values\Rule lowercase() Преобразует строку в нижний регистр
 * @method static \boolive\values\Rule uppercase() Преобразует строку в верхний регистр
 * @method static \boolive\values\Rule condition() Условие поиска или валидации объекта
 *
 * Методы добавления фильтра к объекту правила.
 * @method \boolive\values\Rule max($max) Максимальное значение. Правая граница отрезка. Максимальный размер массива
 * @method \boolive\values\Rule min($min) Минимальное значение. Левая граница отрезка. Минимальный размер массива
 * @method \boolive\values\Rule less($less) Меньше указанного значения. Правая граница интервала. Размер массива меньше указанного
 * @method \boolive\values\Rule more($more) Больше указанного значения. Левая граница интервала. Размер массива больше указанного
 * @method \boolive\values\Rule eq() Равен указанному значению
 * @method \boolive\values\Rule not() Не равен указанному значению
 * @method \boolive\values\Rule in() Допустимые значения. Через запятую или массив
 * @method \boolive\values\Rule not_in() Недопустимые значения. Через запятую или массив
 * @method \boolive\values\Rule required() Должен существовать
 * @method \boolive\values\Rule default($value) Значение по умолчанию, если есть ошибки. Ошибка удаляется
 * @method \boolive\values\Rule ignore() Коды игнорируемых ошибок
 * @method \boolive\values\Rule trim() Обрезание строки
 * @method \boolive\values\Rule escape() Экранирование html символов
 * @method \boolive\values\Rule striptags() Вырезание html тегов
 * @method \boolive\values\Rule email() Email адрес
 * @method \boolive\values\Rule url() URL
 * @method \boolive\values\Rule uri() URI = URL + URN, возможно отсутсвие части URL или URN
 * @method \boolive\values\Rule ip() IP
 * @method \boolive\values\Rule regexp() Проверка на совпадение одному из регулярных выражений. Выражения через запятую или массив
 * @method \boolive\values\Rule ospatterns() Проверка на совпадения одному из паттернов в стиле оболочки операционной системы: "*gr[ae]y". Паттерны запятую или массив
 * @method \boolive\values\Rule color() HEX формат числа. Код цвета #FFFFFF. Возможны сокращения и опущение #
 * @method \boolive\values\Rule file_upload() Информация о загружаемом файле в виде массива
 * @method \boolive\values\Rule lowercase() Преобразует строку в нижний регистр
 * @method \boolive\values\Rule uppercase() Преобразует строку в верхний регистр
 * @method \boolive\values\Rule condition() Условие поиска или валидации объекта
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
     * @return \boolive\values\Rule Новый объект правила
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
     * @return \boolive\values\Rule
     */
    function __call($name, $args)
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
    function &__get($name)
    {
        return $this->filters[$name];
    }

    /**
     * Установка фильтра через присвоение
     * @example $rule->max = 10; //установка фильтра max с аргументом 10
     * @param $name Название фильтра
     * @param mixed $args Массив аргументов. Если не является массивом, то значение будет помещено в массив
     */
    function __set($name, $args)
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
    function __isset($name)
    {
        return isset($this->filters[$name]);
    }

    /**
     * Удаление фильтра
     * @example unset($rule->max);
     * @param $name Название фильтра
     */
    function __unset($name)
    {
        unset($this->filters[$name]);
    }

    /**
     * Выбор всех фильтров
     * @return array Ассоциативный массив фильтров, где ключ элемента - название фильтра, а значение - аргументы фильтра
     */
    function getFilters()
    {
        return $this->filters;
    }

    function trace()
    {
        return $this->filters;
    }
}