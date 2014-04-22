<?php
/**
 * Виджет для отображения единичного ключевого слова
 *
 * @version 1.0
 * @author Azat Galiev <AzatXaker@gmail.com>
 */

namespace site\library\content_widgets\Keyword;

use \site\library\views\Widget\Widget;

class Keyword extends Widget
{
    function show($v = array(), $commands, $input)
    {
        $keyword = $this->_input['REQUEST']['object']->linked();
        $v['title'] = $keyword->title->value();
        $v['href'] = $keyword->uri();
        //$this->_commands->htmlHead('meta', array('name'=>'keywords', 'content'=>$v['title']));
        return parent::show($v, $commands, $input);
    }
}