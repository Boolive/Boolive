<?php
/**
 * Виджет отображения комментария
 *
 * @version 1.0
 * @author Azat Galiev <AzatXaker@gmail.com>
 */

namespace Library\content_widgets\Comment;

use Library\views\Widget\Widget,
    Boolive\values\Rule,
    Boolive\data\Data;

class Comment extends Widget
{
    public function work($v = array())
    {
        $object = $this->_input['REQUEST']['object'];
        $v['text'] = $object->text->getValue();
        $v['author'] = $object->author;
        $v['sub_comments'] = null;
        $object->findAll2(array(
                'where' => array(
                    array('attr', 'is_history', '=', 0),
                    array('attr', 'is_delete', '=', 0),
                ),
                'order' => array(
                    array('order', 'ASC')
                )
            ), true);
        $i = 1;
        $sub_comments_flag = false;
        foreach ($object->_children as $child) {
            if ($child->is('/Library/content_samples/Comment')) {
                $sub_comments_flag = true;
                $this->_input_child['REQUEST']['object']->{'comment' . $i} = $child;
            }
            $i++;
        }
        if ($sub_comments_flag) {
            $this->_input_child['show_title'] = false;
            $v['sub_comments'] = Data::read('/Library/content_widgets/Comments')->start($this->_commands, $this->_input_child);
        }
        return parent::work($v);
    }
}
