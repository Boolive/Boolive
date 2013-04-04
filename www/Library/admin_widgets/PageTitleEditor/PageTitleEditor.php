<?php
/**
 * Виджет редактирования заголовка страницы
 * @author: polinа Putrolaynen
 * @version 1.0
 */
namespace Library\admin_widgets\PageTitleEditor;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class PageTitleEditor extends Widget
{
    public function getInputRule()
   {
       return Rule::arrays(array(
              'REQUEST' => Rule::arrays(array(
                      'object' => Rule::entity()->required(),
                      'call' => Rule::string()->default('')->required(),
                      'Page' => Rule::arrays(Rule::string())
                      )
                   )
                )
            );
   }

    public function work($v = array())
    {
        // Сохранение атрибутов
        if ($this->_input['REQUEST']['call'] == 'save'){
            return $this->callSave();
        }
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        $v['value'] = $this->_input['REQUEST']['object']->value();
        return parent::work($v);
    }

    /**
     * Сохранение атрибутов объекта
     * @return mixed
     */
    protected function callSave()
    {
        $v = array();
        if ($this->_input['REQUEST']['Page']['title']==''){
            $v['error']['value'] = 'Нельзя сохранить пустое название';
        }else{
            $obj  = $this->_input['REQUEST']['object'];
            $obj->value($this->_input['REQUEST']['Page']['title']);
            $error = null;
            $obj->save(false, false, $error);
            if (isset($error) && $error->isExist()){
                $v['error'] = array();
                if ($error->isExist('_attribs')){
                   foreach ($error->_attribs as $key => $e){
                       /** @var $e \Boolive\errors\Error */
                       $v['error'][$key] = $e->getUserMessage(true,' ');
                   }
                   $error->delete('_attribs');
               }
               $v['error']['_other_'] = $error->getUserMessage(true);
            }
            $v['title'] = $obj->value();
        }
        return $v;
    }
}