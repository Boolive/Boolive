<?php
/**
 * Файл
 * 
 * @version 1.0
 */
namespace site\library\admin_widgets\FileEditor;

use boolive\commands\Commands;
use boolive\data\Entity;
use boolive\file\File;
use boolive\values\Rule;
use site\library\views\Widget\Widget;

class FileEditor extends Widget
{
    // function startRule()
    // {
    //     $rule = parent::startRule();
    //     $rule->arrays[0]['REQUEST']->arrays[0]['select'] = Rule::in('file')->required();
    //     return $rule;
    // }

    function startCheck(Commands $commands, $input)
    {
        if (parent::startCheck($commands, $input)){
            return preg_match('/\.(txt|css|js|tpl)$/ui', $this->_input['REQUEST']['object']->file());
        }
        return false;
    }

    function show($v = array(), $commands, $input)
    {
        /** @var Entity $obj */
        $obj = $this->_input['REQUEST']['object'];
        if ($content = $obj->fileContent(false, false)){
            if ($obj->isDefaultValue()){
                $v['content']['default'] = $content['content'];
                if (!($v['content']['self'] = $obj->fileTemplate())){
                    $v['content']['self'] = $content['content'];
                };
            }else{
                $v['content']['self'] = $content['content'];
                if (($p = $obj->proto()) && ($defcont = $p->fileContent(false, false))){
                    $v['content']['default'] = $defcont['content'];
                }else{
                    $v['content']['default'] = '';
                }
            }
            $v['file'] = $obj->file();
            $v['file-ext'] = File::fileExtention($v['file']);
        }else{
            $v['content'] = array('','');
            $v['file'] = '';
            $v['file-ext'] = '';
        }
        $v['is_default_value'] = $obj->isDefaultValue();
        $v['data-o'] = $obj->uri();
        $v['title'] = $obj->title->inner()->value();
        return parent::show($v,$commands, $input);
    }
}