<?php
/**
 * Импорт
 * 
 * @version 1.0
 */
namespace Site\library\admin_widgets\Import;

use Boolive\data\Data;
use Boolive\data\Entity;
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
        $handlers = $this->handlers->inner()->find(array('where'=>array('attr','is_property','=',0)));
        $find = false;
        $i=-1;
        $cnt=count($handlers);
        $params = array(
            'parent' => $object,
            'file' => $file
        );
        while (!$find && ++$i<$cnt){
            $find = $handlers[$i]->linked()->usageCheck($params);
        }
        if ($find){
            /** @var Entity $proto */
            $proto = $handlers[$i]->linked();
            $tasks = Data::read('/system/tasks');
            $task = $proto->birth($tasks, true);
            $task->where->proto($object);
            $task->file->file($file);
            $task->save();
            $task->isDraft(false);
            $task->save(false, false);
            return $task->id();
        }
        return array(
            'error' => 'Не найден обработчик для выбранного файла. Выберите другой тип файла'
        );
    }

    function getTasks($object)
    {
        // Поиск задач прототипированных от InfoImport и со свойством where равным $object->key()
        $tasks = Data::read(array(
            'from' => '/system/tasks',
            'select' => 'children',
            'depth' => array(1,1),
            'where' => array(
                array('is','/library/admin_widgets/Import/handlers/ImportFile'),
                array('child','where',array('is',$object->id()))
            )
        ));
        $have_process = false;
        $list = array();
        foreach ($tasks as $key => $item){
            $list[$key] = array(
                'title' => $item->title->value(),
                'status' => $item->value(),
                'status_msg' => ''
            );
            switch ($list[$key]['status']){
                case 0: $list[$key]['status_msg'] = 'В очереди'; break;
                case 1: $list[$key]['status_msg'] = 'Выполняется'; break;
                case 2: $list[$key]['status_msg'] = 'Ошибка'; break;
                case 3: $list[$key]['status_msg'] = 'Выполнено'; break;
            }
            $r = trim($item->report->value());
            if ($r) $list[$key]['status_msg'].=': '.$r;
            if ($list[$key]['status'] == 1 && $item->percent->value() > 0){
                $list[$key]['status_msg'].=' '.sprintf('%0.2f',$item->percent->value()).'%';
            }
            $have_process = $have_process || $list[$key]['status']< 2;
        }
        return array(
            'list' => array_reverse($list),
            'have_process' => $have_process
        );
    }

    function clearTasks($object)
    {
        $tasks = Data::read(array(
            'from' => '/system/tasks',
            'select' => 'children',
            'depth' => array(1,1),
            'where' => array(
                array('attr','value','in',array(2, 3)),
                array('is','/library/admin_widgets/Import/handlers/ImportFile'),
                array('child','where',array('is', $object->id()))
            )
        ));
        foreach ($tasks as $t){
            /** @var Entity $t */
            $t->destroy();
        }
        return true;
    }
}