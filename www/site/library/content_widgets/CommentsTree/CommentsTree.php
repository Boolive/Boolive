<?php
/**
 * Комментарии
 * Отображает дерево комментариев
 * @version 1.0
 */
namespace site\library\content_widgets\CommentsTree;

use boolive\auth\Auth;
use boolive\data\Data2;
use boolive\values\Rule;
use site\library\views\AutoWidgetList2\AutoWidgetList2;

class CommentsTree extends AutoWidgetList2
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity($this->object_rule->value())->required(),
                'call' => Rule::string()->default('')->required(),
                'add'=> Rule::arrays(array(
                    'parent' => Rule::entity()->required(),
                    'message' => Rule::string()->required()
                ))
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        if ($this->_input['REQUEST']['call'] == 'add'){
            $comment = Data2::read('/library/content_samples/Comment')->birth($this->_input['REQUEST']['add']['parent']);
            $comment->isDraft(false);
            $comment->message->value($this->_input['REQUEST']['add']['message']);
            $comment->author->proto(Auth::getUser());
            $comment->save();

            $this->_input_child['REQUEST']['object'] = $comment;
            return $this->startChild('views');
        }else{
            $v['object'] = $this->_input['REQUEST']['object']->key();
            return parent::show($v,$commands, $input);
        }
    }
}