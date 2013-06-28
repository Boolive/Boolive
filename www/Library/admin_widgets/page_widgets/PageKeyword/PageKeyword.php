<?php
/**
 * Отображает одно ключевое слово страницы
 * @author: polinа Putrolaynen
 * @date: 28.03.13
 *
 */
namespace Library\admin_widgets\page_widgets\PageKeyword;

use Boolive\data\Data;
use Library\views\Widget\Widget,
    Boolive\values\Rule;

class PageKeyword extends Widget
{
    public function defineInputRule()
      {
          $this->_input_rule = Rule::arrays(array(
                 'REQUEST' => Rule::arrays(array(
                         'object' => Rule::entity()->required(),
                         'call'=> Rule::string()->default('')->required()
                         )
                      )
                   )
               );
      }

    public function work($v = array())
     {
         // Удаление ключевого слова
        if ($this->_input['REQUEST']['call'] == 'Delete'){
            $this->_input['REQUEST']['object']->isDelete(true);
            $this->_input['REQUEST']['object']->save();
            return true;
        }
         $v['object'] = $this->_input['REQUEST']['object']->uri();
         $v['name'] = $this->_input['REQUEST']['object']->name();
         return parent::work($v);
     }
}