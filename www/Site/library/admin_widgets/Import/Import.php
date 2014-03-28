<?php
/**
 * Импорт
 * 
 * @version 1.0
 */
namespace Site\library\admin_widgets\Import;

use Boolive\file\File;
use Boolive\functions\F;
use Boolive\tasks\Tasks;
use Boolive\values\Rule;
use Site\library\views\Widget\Widget;

class Import extends Widget
{
    function startRule()
    {
        $rule = parent::startRule();
        $rule->arrays[0]['REQUEST']->arrays[0]['call'] = Rule::string()->default('')->required();
        $rule->arrays[0]['FILES']->arrays[0]['import'] = Rule::arrays(Rule::string());
        return $rule;
    }

    function show($v = array(), $commands, $input)
    {
        if ($this->_input['REQUEST']['call'] == 'add_task' && isset($this->_input['FILES']['import'])){
            return $this->addTask($this->_input['FILES']['import'], $this->_input['REQUEST']['object']);
        }else
        if ($this->_input['REQUEST']['call'] == 'get_tasks'){
            return $this->getTasks($this->_input['REQUEST']['object']);
        }else
        if ($this->_input['REQUEST']['call'] == 'clear_tasks'){
            return $this->clearTasks($this->_input['REQUEST']['object']);
        }else
        if (empty($this->_input['REQUEST']['call'])){
            $v['data-o'] = $this->_input['REQUEST']['object']->uri();
            $v['title'] = $this->title->value();
            $tasks = $this->getTasks($this->_input['REQUEST']['object']);
            $v['tasks'] = $tasks['list'];
            return parent::show($v,$commands, $input);
        }
        return false;
    }

    function addTask($file, $object)
    {
        $uniqname = uniqid().'_'.F::translit($file['name']);
        File::upload($file['tmp_name'], DIR_SERVER_TEMP.'import/'.$uniqname);
        return Tasks::add(1, $this->key().'::processTask', $object->key(), 'Импорт файла '.$file['name'], array(
            'file'=>$uniqname,
            'object'=>$object->key(),
        ));
    }

    function getTasks($object)
    {
        $list = Tasks::find($this->key().'::processTask', $object->key());
        $have_process = false;
        foreach ($list as $key => $item){
            switch ($item['status']){
                case Tasks::STATUS_WAIT: $list[$key]['status_msg'] = 'В очереди'; break;
                case Tasks::STATUS_PROCESS: $list[$key]['status_msg'] = 'Выполняется'; break;
                case Tasks::STATUS_ERROR: $list[$key]['status_msg'] = 'Ошибка'; break;
                case Tasks::STATUS_SUCCESS: $list[$key]['status_msg'] = 'Выполнено'; break;
            }
            if ($item['report']) $list[$key]['status_msg'].=': '.$item['report'];
            if ($item['status'] == Tasks::STATUS_PROCESS && $item['percent']>0){
                $list[$key]['status_msg'].=' '.$item['percent'].'%';
            }
            $have_process = $have_process || $item['status'] < Tasks::STATUS_ERROR;
        }
        return array(
            'list' => array_reverse($list),
            'have_process' => $have_process
        );
    }

    function clearTasks($object)
    {
        return Tasks::clear(null, $this->key().'::processTask', $object->key());
    }

    function processTask($id, $params)
    {

    }
}