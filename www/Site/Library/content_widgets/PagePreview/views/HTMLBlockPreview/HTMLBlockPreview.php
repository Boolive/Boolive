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

        $tags = array(); // массив, в который записываем кол-во открытых тэгов

        $r = preg_split('/(<[^>]*>)/im',$text,-1,PREG_SPLIT_DELIM_CAPTURE);

        $c =0 ; // счетчик длины строки
        $mx = (mb_strpos($text,'<a class="more"> </a>') ? mb_strpos($text,'<a class="more"> </a>') : $len);; // макс. длина текста
        $cw = true; // достигли ли макс. - можно заменить на ($mx>$c)
        $res = ''; // результат

        foreach ($r as $w) {

            // если тэг
            if (preg_match('/<(\/?)([a-z]+)\s*[^>]*>/ims',$w,$m)) {
                if ($m[1]!='/') { // открывающий

                    if ($cw){ // если еще не макс., то пишем, добавляем в счетчик тэгов
                        $tags[mb_strtolower($m[2])]++;
                        $res.=$w;
                    }
                } else { // закрывыющий тэг

                    // уменьшаем счетчик , если есть такой открытый тэг - пишем
                    if ($tags[mb_strtolower($m[2])]>0) {
                        $res.=$w;
                    }
                    $tags[mb_strtolower($m[2])]--;
                }
            }
            if ($cw) {
                $len = mb_strlen($w); // длина текста
                if ($len+$c>$mx) { // достигли?
                    // обрезаем, добавляем ...
                    $res.=mb_substr($w,0,$mx-$c);
                    $cw = false;
                }else {

                    $res.=$w;
                    $c+=$len;
                }
            }
        }

        $output = $res;

        $v['object'] = $output;

        return Widget::show($v, $commands, $input);
    }
}