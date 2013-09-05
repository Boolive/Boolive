<?php
/**
 * Вид
 * Базовый объект для создания элементов интерфейса
 * @version 1.0
 */
namespace Library\admin_widgets\ToggleAction;

use Library\views\View\View,
    Boolive\values\Rule;

class ToggleAction extends View
{
    /**
     * @var bool Текущее состояние действия (для текущего отображаемого объекта)
     */
    protected $_state;

    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity()),
                            Rule::entity()
                        )->required(),
                        'call' => Rule::string()->default('')->required(),
                    )
                )
            )
        );
    }

    /**
     * Инициализация состояния.
     * Необходимо переопредлить в наследниках класса
     */
    protected function initState()
    {
        $this->_state = false;
    }

    /**
     * Текущее состояние действия
     * @return bool
     */
    public function state()
    {
        return $this->_state;
    }

    /**
     * Выполнение действия
     */
    public function toggle()
    {
        return false;
    }

    protected function initInput($input)
    {
        parent::initInput($input);
        if (!isset($this->_input_error)){
            $this->initState();
        }
    }

    public function work($v = array())
    {
        if ($this->_input['REQUEST']['call'] == 'toggle'){
            return $this->toggle();
        }
        return parent::work($v);
    }
}