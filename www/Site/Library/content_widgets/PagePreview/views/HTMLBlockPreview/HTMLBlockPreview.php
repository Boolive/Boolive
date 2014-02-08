<?php
/**
 * Виджет HTML блока
 * Формирует результат работы спомощью шаблонизации. Шаблоном является значение виджета
 * @version 1.0
 */
namespace Library\content_widgets\PagePreview\views\HTMLBlockPreview;

use Library\views\Widget\Widget;
use Library\views\HtmlBlock\HtmlBlock;

class HTMLBlockPreview extends HtmlBlock
{
    function show($v = array(), $commands, $input)
    {
        $v['object'] = $this->_input['REQUEST']['object']->value();
        $text = $v['object'];
        $len = mb_strlen($text);
        // массив, в который записываем кол-во открытых тэгов
        $tags = array();
        $r = preg_split('/(<[^>]+>)/im', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        // счетчик длины строки
        $c = 0;
        // макс. длина текста
        $mx = mb_strpos($text,'<a class="more"> </a>')>0 ? mb_strpos($text,'<a class="more"> </a>') : $len;
        // достигли ли макс. - можно заменить на ($mx>$c)
        $cw = true;
        // результат
        $res = '';
        foreach ($r as $w) {
            // если тэг
            if (preg_match('/<(\/?)([a-z]+)\s*[^>]*>/ims',$w,$m)) {
                $key = mb_strtolower($m[2]);
                if (!isset($tags[$key])) $tags[$key] = 0;
                if ($m[1] != '/'){
                    // открывающий
                    if ($cw){
                        // если еще не макс., то пишем, добавляем в счетчик тэгов
                        $tags[$key]++;
                        $res.= $w;
                    }
                }else{
                    // закрывыющий тэг
                    // уменьшаем счетчик , если есть такой открытый тэг - пишем
                    if ($tags[$key] > 0) {
                        $res.= $w;
                    }
                    $tags[$key]--;
                }
            }
            if ($cw){
                // длина текста
                $len = mb_strlen($w);
                // достигли?
                if ($len+$c>$mx){
                    // обрезаем, добавляем ...
                    $res.=mb_substr($w,0,$mx-$c);
                    $cw = false;
                }else{
                    $res.=$w;
                    $c+=$len;
                }
            }
        }

        $v['object'] = $res;
        return Widget::show($v, $commands, $input);
    }
}