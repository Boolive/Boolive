<?php
/**
 * Набор общих функций
 * 
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\functions;

class F
{
    /**
     * Парсер. Вставка в шаблон значений
     *
     * @param string $text Шаблон текста
     * @param array $vars Массив значений для вставки в шаблон
     * @param string $tpl_left
     * @param string $tpl_right
     * @return string Подготовленный текст
     */
    static function parse($text, $vars=array(), $tpl_left = '{', $tpl_right = '}')
    {
        $vars = filter_var_array($vars, FILTER_SANITIZE_SPECIAL_CHARS);
        // По циклу проходимся по всем переменным заменяя значения в {} на значения в массиве
        if (is_array($vars)){
            foreach ($vars as $key => $value){
                $text = str_replace($tpl_left.$key.$tpl_right, $value, $text);
            }
        }
        return $text;
    }

    /**
     * Рекурсивное объединение массива с учетом числовых ключей
     * @param array
     * @return array
     */
    static function arrayMergeRecursive()
    {
        $params = func_get_args();
        $return = array_shift($params);
        foreach ($params as $array){
            foreach ($array as $key => $value){
                if (isset($return[$key]) && is_array($value) && is_array($return[$key])){
                    $return[$key] = self::arrayMergeRecursive($return[$key], $value);
                }else{
                    $return[$key] = $value;
                }
            }
        }
        return $return;
    }

    /**
     * Разрезание строки на одномерный массив по разделительной строке.
     * Если $lim отрицателен, то разрезание происходит с конца строки
     * @param $delim Разделительная строка
     * @param $str Строка, которую нужно разрезать
     * @param int $lim Максимальное количество частей
     * @return array
     */
    static function explode($delim, $str, $lim = 1)
    {
        if ($lim > -2) return explode($delim, $str, abs($lim));
        $lim = -$lim;
        $out = explode($delim, $str);
        if ($lim >= count($out)) return $out;
        $out = array_chunk($out, count($out) - $lim + 1);
        return array_merge(array(implode($delim, $out[0])), $out[1]);
    }

    /**
     * Разделение строки на две части используя строку-разделитель
     * Поиск разделителя выполняется с конца
     * @param $delim Разделитель
     * @param $str Строка, которая делится
     * @return array Массив строк. Если разделитель не найден, то первая строка = null, вторая = $str
     */
    static function splitRight($delim, $str)
    {
        $pos = mb_strrpos($str, $delim);
        if ($pos === false) return array(null, $str);
        return array(mb_substr($str, 0, $pos), mb_substr($str, $pos+1));
    }

    /**
     * Объединение элементов массива в строку
     * Работает с многомерныыми массивами
     * @param $glue Соединительная строка
     * @param $pieces Массив
     * @return string
     */
    static function implodeRecursive($glue, $pieces)
    {
        $items = array();
        foreach ($pieces as $item){
            if (is_array($item)){
                $items[] = self::implodeRecursive($glue, $item);
            }else{
                $items[] = $item;
            }
        }
        return implode($glue, $items);
    }

    /**
     * Проверка совпадения массивов
     * Если элементы массива $mask имеются в $source и их значения равны, то массивы совпадают,
     * при этом в массиве $source могут содержаться элементы, которых нет в $mask
     * @param array $source Проверяемый массив
     * @param array $mask Массив-маска
     * @return bool
     */
    static function isArrayMatch($source, $mask)
    {
        if (!is_array($source) || !is_array($mask)){
            return false;
        }
        foreach ($mask as $key => $value){
            if (!isset($source[$key])){
                return false;
            }else
            if (is_array($value)){
                if (is_array($source[$key])){
                    return self::isArrayMatch($value, $source[$key]);
                }else{
                    return false;
                }
            }else
            if ($source[$key] != $value){
                return false;
            }
        }
        return true;
    }
}
