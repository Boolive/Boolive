<?php
/**
 * Логика
 * 
 * @version 1.0
 */
namespace site\library\admin_widgets\LogicEditor;

use boolive\data\Entity;
use boolive\values\Rule;
use site\library\views\Widget\Widget;

class LogicEditor extends Widget
{
    // function startRule()
    // {
    //     $rule = parent::startRule();
    //     $rule->arrays[0]['REQUEST']->arrays[0]['select'] = Rule::in('logic')->required();
    //     return $rule;
    // }

    function show($v = array(), $commands, $input)
    {
        /** @var Entity $obj */
        $obj = $this->_input['REQUEST']['object'];

        if ($content = $obj->classContent(false, false)){
            if ($obj->isDefaultClass()){
                $v['content']['default'] = $content['content'];
                if (!($v['content']['self'] = $obj->classTemplate())){
                    $v['content']['self'] = $content['content'];
                };
            }else{
                $v['content']['self'] = $content['content'];
                if (($p = $obj->proto()) && ($defcont = $p->classContent(false, false))){
                    $v['content']['default'] = $defcont['content'];
                }else{
                    $v['content']['default'] = '';
                }
            }
        }else{
            $v['content'] = array('','');
        }
        $v['is_default_class'] = $obj->isDefaultClass();

        $v['data-o'] = $obj->uri();
        $v['title'] = $obj->title->inner()->value();
        return parent::show($v,$commands, $input);
    }


}