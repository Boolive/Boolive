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
    public function getInputRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'method' => Rule::is('POST'),
                'view' => Rule::entity()->required(),
                )
            ))
        );
    }

    public function work()
    {
        $v = array();
        $v['out'] = $this->_input['REQUEST']['view']->start($this->_commands, $this->_input_child);
		// Вместе с html нужно передать пути на файлы css и js
        foreach ($this->_commands->get('addHtml') as $com){
            if (in_array($com[0], array("link", "script"/*, "meta", "title", "style"*/))){
                if (isset($com[1]['text'])){
                    $text = $com[1]['text'];
                    unset($com[1]['text']);
                }
                if (isset($com[1]['src'])) $com[1]['src'] = $com[1]['src'].'?'.TIMESTAMP;
                if (isset($com[1]['href'])) $com[1]['href'] = $com[1]['href'].'?'.TIMESTAMP;
                $attr = '';
                foreach ($com[1] as $name => $value) $attr.=' '.$name.'="'.$value.'"';

                if (($com[0] == "link" || $com[0] == "script") && !isset($text)){
                    if (isset($com[1]['src'])) $v['links'][] = $com[1]['src'];
                    if (isset($com[1]['href'])) $v['links'][] = $com[1]['href'];
                }
            }
        }
        header('Content-type: application/json');
		echo json_encode($v);
    }
}
