<?php
/**
 * Добавить
 * Предоставляет выбор объекта для добавления его в отображаемый объект
 * @version 1.0
 */
namespace Library\admin_widgets\Add;

use Library\views\AutoWidgetList\AutoWidgetList,
    Boolive\values\Rule;

class Add extends AutoWidgetList
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity()->required(),
                        'call' => Rule::string()->default('')->required(),
                        'proto' => Rule::entity(),
                    )
                )
            )
        );
    }


    public function work($v = array())
    {
        // Удаление
        if ($this->_input['REQUEST']['call'] == 'add'){
            if (isset($this->_input['REQUEST']['proto']) && isset($this->_input['REQUEST']['object'])){
                /** @var $parent \Boolive\data\Entity */
                $parent = $this->_input['REQUEST']['object'];
                $obj = $parent->add($this->_input['REQUEST']['proto']);
                $parent->save(true, true, $error);
                $v['result'] = array(
                    'object' => $obj->uri(),
                    'parent' => $parent->uri()
                );
            }
            return $v;
        }else{
            $v['title'] = $this->title->value();
            if (!$v['object']['title'] = $this->_input['REQUEST']['object']->title->value()){
                $v['object']['title'] = $this->_input['REQUEST']['object']->name();
            }
            $v['object']['uri'] = $this->_input['REQUEST']['object']->uri();
            return parent::work($v);
        }
    }

    protected function getList($protos){
        // @todo Сделать выбор часто используемых объектов
        $obj = \Boolive\data\Data::read('/Library/basic/simple/Object');
        return array($obj);
    }
}