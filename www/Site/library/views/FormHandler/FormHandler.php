<?php
/**
 * Обработчик форм
 * Принимает запросы от форм. Во входящих данных должен быть параметр form с uri формы (виджета).
 * Клиенту отвечает по умолчанию редиректом на текущий запрос
 * @version 1.0
 */
namespace Site\library\views\FormHandler;

use Boolive\input\Input,
    Site\library\views\View\View,
    Boolive\values\Rule;

class FormHandler extends View
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'form' => Rule::entity()->required()
            )),
            'previous' => Rule::not(true)
        ));
    }

    function work()
    {
        $out = $this->_input['REQUEST']['form']->start($this->_commands, $this->_input_child);
        header("HTTP/1.1 303 See Other");
        if ($redirect = $this->_commands->get('redirect')){
            header('Location: '.$redirect[0][0]);
        }else{
            header('Location: '.Input::url(null,0,array()));//текущий адрес без параметров
        }
        if ($out != false){
            echo $out;
        }
    }
}