<?php
/**
 * Поле обратного адреса
 *
 * @version 1.0
 */
namespace Library\content_widgets\Feedback\switch_views\case_mail\EmailField;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class EmailField extends Widget
{
//    public function getInputRule()
//    {
//        return Rule::arrays(array(
//            'GET' => Rule::arrays(array(
//                'object' => Rule::entity(), // объект, который отображать
//                ), Rule::any() // не удалять другие элементы
//            ),
//            'POST' => Rule::arrays(array(
//                'object' => Rule::entity(), // объект, который отображать
//                $this['uri'] => Rule::string() // значение поял формы
//                ), Rule::any() // не удалять другие элементы
//            )), Rule::any() // не удалять другие элементы
//        );
//    }

    public function work($v = array())
    {
//        //trace($this->_input['POST']);
//        if (isset($this->_input['POST']['object'])){
//            //trace($this->_input['POST']);
//
//            $this->_input['POST']['object']['value'] = $this->_input['POST'][$this['uri']];
//            /** @var $error \Boolive\errors\Error */
//            $error = null;
//            if (!$this->_input['POST']['object']->check($error)){
//                // @todo Универсальная обработка ошибки
//                $list = $error->_attribs->value->getAll();
//                foreach ($list as $e){
//                    trace($e->getMessage());
//                }
//            }
//        }else{
            $v['title'] = $this->_input['REQUEST']['object']->title->getValue();
            $v['value'] = $this->_input['REQUEST']['object']->getValue();
            $v['name'] = 'object[_children]['.$this->_input['REQUEST']['object']->getName().'][value]';
            $v['id'] = $this['uri'];
            return parent::work($v);
//        }
    }
}
