<?php
/**
 * Обработчик направленных запросов
 * По запросу клиента запускается требуемое представление и возвращается результат работы в JSON.
 * Используется для обновления частей страницы без полной их перегрузки, а также для обработки форм и выполнения иных
 * действий со стороны клиента.
 * @version 1.0
 */
namespace Library\views\DirectHandler;

use Library\views\View\View,
    Boolive\values\Rule;

class DirectHandler extends View
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'method' => Rule::eq('POST'),
                'direct' => Rule::entity()->required()
            )),
            'previous' => Rule::not(true)
        ));
    }

    function work()
    {
        $v = array();
        $v['out'] = $this->_input['REQUEST']['direct']->linked()->start($this->_commands, $this->_input_child);
		$v['links'] = array();
        $js = array();
		// Вместе с html нужно передать пути на css и js
        foreach ($this->_commands->get('htmlHead') as $com){
            if (in_array($com[0], array("link", "script"/*, "meta", "title", "style"*/))){
                $attr = '';
                foreach ($com[1] as $name => $value) $attr.=' '.$name.'="'.$value.'"';
                if (($com[0] == "link" || $com[0] == "script")){
                    if (isset($com[1]['src'])) $link = $com[1]['src'];
                    if (isset($com[1]['href'])) $link = $com[1]['href'];
                    if (isset($link)){
                        if ($com[0] == 'script'){
                            $js[] = $link;
                        }else{
                            $v['links'][] = $link;
                        }
                    }
                }else{
                    if (!isset($v[$com[0]])) $v[$com[0]] = array();
                    $v[$com[0]][] = $com[1];
                }
            }
        }
        if (!empty($js)) $v['links'] = array_merge($v['links'], $js);
        if (empty($v['links'])) unset($v['links']);
        header('Content-type: application/json');
		echo json_encode($v);
    }
}
