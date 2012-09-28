<?php
/**
 * Виджет для отображения единичного ключевого слова
 *
 * @version 1.0
 * @author Azat Galiev <AzatXaker@gmail.com>
 */

namespace Library\content_widgets\Keyword;

use \Library\views\Widget\Widget;

class Keyword extends Widget
{
    public function work($v = array())
    {
        $v['keyword'] = $this->_input['REQUEST']['object']->getName();
        return parent::work($v);
    }
}
