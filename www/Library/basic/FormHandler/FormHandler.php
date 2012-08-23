<?php
/**
 * Обработчик форм
 * Принимает запрос и перенапрвляет его для обработки объекту формы.
 *
 * @version 1.0
 */
namespace Library\basic\FormHandler;

use Boolive\data\Entity,
    Boolive\values\Rule,
    Boolive\commands\Commands;

class FormHandler extends Entity
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'POST' => Rule::arrays(array(
                'controller' => Rule::entity()->required(),
                ), Rule::any() // не удалять другие элементы
            ),
            'GET' => Rule::arrays(array(
                'path' => Rule::string()->required(),
                ), Rule::any()
            )), Rule::any() // не удалять другие элементы
        );
    }

    public function work($v = array()){
        $this->_input['POST']['controller']->process($this->_commands, $this->_input);
        if ($redirect = $this->_commands->get('redirect')){
            header('Location: '.$redirect[0][0]);
        }else{
            header('Location: '.$this->_input['GET']['path']);
        }
    }
}