<?php
/**
 * Действие скрытия объектов
 * @version 1.0
 */
namespace Library\admin_widgets\Hiding;

use Library\views\View\View,
    Boolive\values\Rule;

class Hiding extends View
{
    private $_state = '0';

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

    protected function initInput($input)
    {
        parent::initInput($input);
        if (!isset($this->_input_error)){
            $object = is_array($this->_input['REQUEST']['object'])? reset($this->_input['REQUEST']['object']) : $this->_input['REQUEST']['object'];
            $this->_state = $object->isHidden();
        }
    }

    public function work($v = array())
    {
        // Отправка атрибутов
        if ($this->_input['REQUEST']['call'] == 'hide'){
            $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
            foreach ($objects as $o){
                /** @var \Boolive\data\Entity $o */
                $o->isHidden(true);
                // @todo Обрабатывать ошибки
                $o->save();
            }
            $v['result'] = true;
            return $v;
        }
        return parent::work($v);
    }

    public function state()
    {
        return $this->_state;
    }
}