<?php
/**
 * Комментарий
 * Отображает комментарий и ветку подчиненных комментариев (ответы)
 * @version 1.0
 */
namespace site\library\content_widgets\CommentsTree\views\Comment;

use boolive\data\Entity;
use site\library\views\AutoWidgetList2\AutoWidgetList2;

class Comment extends AutoWidgetList2
{
    function show($v = array(), $commands, $input)
    {
        /** @var Entity $obj */
        $obj = $this->_input['REQUEST']['object'];
        $user = $obj->author->linked();
        if ($user->isExist()){
            $v['user']['name'] = $user->name();
            $v['user']['avatar'] = $user->avatar->file();
        }else{
            $v['user']['name'] = 'Аноним';
            $v['user']['avatar'] = false;
        }
        $v['date'] = $obj->date();
        $v['uri'] = $obj->key();
        return parent::show($v,$commands, $input);
    }
}