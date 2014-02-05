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
     static private $translit_table;
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
     * @param bool $single Если в троке встречаются подряд идущие символы-разделители, то не считать их разделителями.
     * @return array Массив строк. Если разделитель не найден, то первая строка = null, вторая = $str
     */
    static function splitRight($delim, $str, $single = false)
    {

//        if ($single){
//            // Только для ASCII символов!!!
//            preg_match_all('/'.preg_quote($delim,'/').'+/ui', $str, $m, PREG_OFFSET_CAPTURE);
//            $i=count($m[0])-1;
//            $pos = false;
//            while ($i>=0){
//                if ($m[0][$i][0] === $delim){
//                    $pos = $m[0][$i][1];
//                    $i = -1;
//                }else{
//                    $i--;
//                }
//            }
//        }else{
//            $pos = mb_strrpos($str, $delim);
//        }
//        if ($pos === false) return array(null, $str);
//        return array(mb_substr($str, 0, $pos), mb_substr($str, $pos+mb_strlen($delim)));

        if ($single){
            $all_pos = array();
            mb_regex_encoding("UTF-32");
            mb_ereg_search_init(mb_convert_encoding($str, "UTF-32", "UTF-8"), mb_convert_encoding($delim.'+', "UTF-32", "UTF-8"));
            while ($r = mb_ereg_search_pos()) $all_pos[] = array($r[0]/4, $r[1]/4);
            mb_regex_encoding("UTF-8");
            $i = count($all_pos)-1;
            $pos = false;
            while ($i >= 0){
                if (mb_substr($str, $all_pos[$i][0], $all_pos[$i][1]) === $delim){
                    $pos = $all_pos[$i][0];
                    $i = -1;
                }else{
                    $i--;
                }
            }
        }else{
            $pos = mb_strrpos($str, $delim);
        }
        if ($pos === false) return array(null, $str);
        return array(mb_substr($str, 0, $pos), mb_substr($str, $pos+mb_strlen($delim)));
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

    /**
     * Код символа в UTF-8 строке
     *
     * @param string $chr символ
     * @return int
     */
    static function ord($chr)
    {
        $ord0 = ord($chr);
        if ($ord0 >= 0 && $ord0 <= 127){
            return $ord0;
        }
        if (!isset($chr{1})){
            trigger_error('Short sequence - at least 2 bytes expected, only 1 seen');
            return FALSE;
        }
        $ord1 = ord($chr{1});
        if ($ord0 >= 192 && $ord0 <= 223){
            return ( $ord0 - 192 ) * 64 + ( $ord1 - 128 );
        }
        if (!isset($chr{2})){
            trigger_error('Short sequence - at least 3 bytes expected, only 2 seen');
            return FALSE;
        }
        $ord2 = ord($chr{2});
        if ($ord0 >= 224 && $ord0 <= 239){
            return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
        }
        if (!isset($chr{3})){
            trigger_error('Short sequence - at least 4 bytes expected, only 3 seen');
            return FALSE;
        }
        $ord3 = ord($chr{3});
        if ($ord0 >= 240 && $ord0 <= 247){
            return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2 - 128) * 64 + ($ord3 - 128);
        }
        if (!isset($chr{4})){
            trigger_error('Short sequence - at least 5 bytes expected, only 4 seen');
            return FALSE;
        }
        $ord4 = ord($chr{4});
        if ($ord0 >= 248 && $ord0 <= 251){
            return ($ord0 - 248) * 16777216 + ($ord1 - 128) * 262144 + ($ord2 - 128) * 4096 + ($ord3 - 128) * 64 + ($ord4 - 128);
        }
        if (!isset($chr{5})){
            trigger_error('Short sequence - at least 6 bytes expected, only 5 seen');
            return FALSE;
        }
        if ($ord0 >= 252 && $ord0 <= 253){
            return ($ord0 - 252) * 1073741824 + ($ord1 - 128) * 16777216 + ($ord2 - 128) * 262144 + ($ord3 - 128) * 4096 + ($ord4 - 128) * 64 + (ord($chr{5}) - 128);
        }
        if ($ord0 >= 254 && $ord0 <= 255){
            trigger_error('Invalid UTF-8 with surrogate ordinal '.$ord0);
            return FALSE;
        }
        return null;
    }

    /**
     * Транслит с русского в английский
     * @param $string
     * @return string
     */
    static function translit($string)
    {
        if (!isset(self::$translit_table)){
            self::$translit_table = array(
                'А' => 'A',		'Б' => 'B',		'В' => 'V',		'Г' => 'G',		'Д' => 'D',
                'Е' => 'E',		'Ё' => 'YO',	'Ж' => 'ZH',	'З' => 'Z',		'И' => 'I',
                'Й' => 'J',		'К' => 'K',		'Л' => 'L',		'М' => 'M',		'Н' => 'N',
                'О' => 'O',		'П' => 'P',		'Р' => 'R',		'С' => 'S',		'Т' => 'T',
                'У' => 'U',		'Ф' => 'F',		'Х' => 'H',		'Ц' => 'C',		'Ч' => 'CH',
                'Ш' => 'SH',	'Щ' => 'CSH',	'Ь' => '',		'Ы' => 'Y',		'Ъ' => '',
                'Э' => 'E',		'Ю' => 'YU',	'Я' => 'YA',
                'а' => 'a',		'б' => 'b',		'в' => 'v',		'г' => 'g',		'д' => 'd',
                'е' => 'e',		'ё' => 'yo',	'ж' => 'zh',	'з' => 'z',		'и' => 'i',
                'й' => 'j',		'к' => 'k',		'л' => 'l',		'м' => 'm',		'н' => 'n',
                'о' => 'o',		'п' => 'p',		'р' => 'r',		'с' => 's',		'т' => 't',
                'у' => 'u',		'ф' => 'f',		'х' => 'h',		'ц' => 'c',		'ч' => 'ch',
                'ш' => 'sh',	'щ' => 'csh',	'ь' => '',		'ы' => 'y',		'ъ' => '',
                'э' => 'e',		'ю' => 'yu',	'я' => 'ya',
            );
        }
        return @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', str_replace(array_keys(self::$translit_table), array_values(self::$translit_table), $string));
    }

    /**
     * Декодирование unicode-заменители в символы
     * @param $str
     * @return mixed
     */
    static function special_unicode_to_utf8($str)
    {
        $str = preg_replace('#\\\/#', '/', $str);
        return preg_replace_callback("/\\\u([[:xdigit:]]{4})/i", function($matches){
            $ewchar = $matches[1];
            $binwchar = hexdec($ewchar);
            $wchar = chr(($binwchar >> 8) & 0xFF) . chr(($binwchar) & 0xFF);
            return iconv("unicodebig", "utf-8", $wchar);
        }, $str);
    }

    /**
     * Ковертирование в строку JSON формата
     * @param mixed $value
     * @return mixed|string
     */
    static function toJSON($value, $format = true)
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $f = JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE;
            if ($format) $f = $f|JSON_PRETTY_PRINT;
            return json_encode($value, $f);
        }else{
            return F::special_unicode_to_utf8(json_encode($value));
        }
    }

    /**
     * Загрзка файла конфигурации
     * @param $file
     * @return array
     */
    static function loadConfig($file, $var_name = 'config')
    {
        if (is_file($file)){
            include $file;
            if (isset($$var_name)) return $$var_name;
        }
        return array();
    }

    /**
     * Экранирование строки для сравнения оператором LIKE
     * @param $value
     * @return string
     */
    static function escapeLike($value)
    {
        return strtr($value, array(
            '%' => '\%',
            '_' => '\_'
        ));
    }
}
