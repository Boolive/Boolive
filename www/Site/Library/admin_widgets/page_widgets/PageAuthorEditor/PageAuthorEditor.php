<?php
/**
 * Виджет для выбора автора для страницы
 * @author: polinа Putrolaynen
 * @date: 22.04.13
 *
 */
namespace Library\admin_widgets\page_widgets\PageAuthorEditor;

use Library\views\Widget\Widget,
    Boolive\values\Rule;
class PageAuthorEditor extends Widget {
    public function defineInputRule()
           {
               $this->_input_rule = Rule::arrays(array(
                      'REQUEST' => Rule::arrays(array(
                              'object' => Rule::entity()->required(),
                              'call' => Rule::string()->default('')->required(),
                              'user' => Rule::arrays(Rule::string()),
                              )
                           )
                        )
                    );
           }

    public function work($v = array())
       {
           if (!empty($this->_input['REQUEST']['call'])){
                 //Изменение автора страницы
                 if (isset($this->_input['REQUEST']['user'])){
                     return $this->callChangeAuthor(
                         $this->_input['REQUEST']['user']
                     );
                 }
               return null;
             }
           $v['object'] = $this->_input['REQUEST']['object']->uri();
           $v['value'] = $this->_input['REQUEST']['object']->linked()->name->value();
           $v['title'] = $this->_input['REQUEST']['object']->title->value();
           return parent::work($v);
       }

    protected function callChangeAuthor($user){
           /** @var Entity $obj */
           $obj = $this->_input['REQUEST']['object'];
           $obj->proto($user['user']);
           $obj->isLink(true);
           $obj->save();
           return $obj->name->value();
       }
}