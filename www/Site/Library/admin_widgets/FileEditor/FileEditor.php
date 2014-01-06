<?php
/**
 * Файл
 * 
 * @version 1.0
 */
namespace Library\admin_widgets\FileEditor;

use Boolive\commands\Commands;
use Boolive\file\File;
use Library\views\Widget\Widget;

class FileEditor extends Widget
{
    function startCheck(Commands $commands, $input)
    {
        if (parent::startCheck($commands, $input)){
            return preg_match('/\.(txt|css|js|tpl)$/ui', $this->_input['REQUEST']['object']->file());
        }
        return false;
    }

    function show($v = array(), $commands, $input)
    {
        $v['data-o'] = $this->_input['REQUEST']['object']->uri();
        $v['content'] = file_get_contents($this->_input['REQUEST']['object']->file(null, true));
        $v['file'] = $this->_input['REQUEST']['object']->file();
        $v['file-ext'] = File::fileExtention($v['file']);
        return parent::show($v,$commands, $input);
    }
}