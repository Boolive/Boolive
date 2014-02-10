<?php
/**
 * Меню фильтра
 * 
 * @version 1.0
 */
namespace Site\Library\admin_widgets\BaseExplorer\FilterTool;

use Boolive\session\Session;
use Boolive\values\Rule;
use Site\Library\views\Widget\Widget;

class FilterTool extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'call' => Rule::string(),
                'filter' => Rule::arrays(Rule::string())
            ))
        ));
    }

    function work()
    {
        if (isset($this->_input['REQUEST']['call']) && $this->_input['REQUEST']['call'] == 'saveFilter'){
            $this->saveFilter($this->_input['REQUEST']['filter']);
            return true;
        }else{
            return parent::work();
        }
    }

    function saveFilter($new_filter)
    {
        $filter = $this->filter->linked();
        $filter->real->value(!empty($new_filter['real']));
        $filter->hidden->value(!empty($new_filter['hidden']));
        $filter->draft->value(!empty($new_filter['draft']));
        $filter->updates->value(!empty($new_filter['updates']));
        $filter->mandatory->value(!empty($new_filter['mandatory']));
        $filter->save();
    }

    function show($v = array(), $commands, $input)
    {
        $filters = $this->filter->linked()->find(array('key'=>'name', 'cache'=>2), true);
        $v['filters'] = array();
        foreach ($filters as $name => $f) {
            if ($f instanceof \Site\Library\basic\Boolean\Boolean) {
                $v['filters'][$name] = array('title' => $f->title->inner()->value(), 'value' => $f->value());
            }
        }
        $v['icon'] = $this->icon->inner()->file();
        return parent::show($v,$commands, $input);
    }
}