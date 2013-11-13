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
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity(array('is', '/Library/content_samples/Keyword'))->required(),
                'call' => Rule::string()->default('')->required()
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        // Удаление ключевого слова
        if ($this->_input['REQUEST']['call'] == 'Delete'){
            $this->_input['REQUEST']['object']->isDraft(true);
            $this->_input['REQUEST']['object']->save();
            // Счётчик использования слова
            $key = $this->_input['REQUEST']['object']->linked();
            $key->value($key->value() - 1);
            $key->save();
            return true;
        }
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        $v['name'] = $this->_input['REQUEST']['object']->linked()->title->value();
        return parent::show($v, $commands, $input);
    }
}