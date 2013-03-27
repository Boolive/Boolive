<?php
/**
 * Экспорт
 * Сохраняет выбранные объекты в файловую систему
 * @version 1.0
 */
namespace Library\admin_widgets\Export;

use Boolive\data\Data;
use Boolive\data\Entity;
use Boolive\develop\Trace;
use Boolive\file\File;
use Boolive\session\Session;
use Library\views\Widget\Widget,
    Boolive\values\Rule;

class Export extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity()),
                            Rule::entity()
                        )->required(),
                        'call' => Rule::string()->default('')->required(),
                        'id' => Rule::string()->default(0)->required()
                    )
                )
            )
        );
    }


    public function work($v = array())
    {
        // Экспорт
        if ($this->_input['REQUEST']['call'] == 'export_init'){
            $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
            return $this->exportInit($objects);
        }else
        if ($this->_input['REQUEST']['call'] == 'export_do'){
            return $this->exportDo($this->_input['REQUEST']['id']);
        }
        // Отображение
        else{
            $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
            $v['data-o'] = array();
            $v['objects'] = array();
            foreach ($objects as $o){
                $item = array();
                if (!($item['title'] = $o->title->value())){
                    $item['title'] = $o->name();
                }
                $item['uri'] = $o->uri();
                $v['objects'][] = $item;
                $v['data-o'][]=$item['uri'];
            }
            $v['data-o'] = json_encode($v['data-o']);
            $v['title'] = $this->title->value();
            if (count($objects)>1){
                $v['question'] = 'Вы действительно желаете экспортировать эти объекты?';
                $v['message'] = 'Объекты и их подчинённые будут сохранены в файлы .info';
            }else{
                $v['question'] = 'Вы действительно желаете экспортировать этот объект?';
                $v['message'] = 'Объект и его подчинённые будут сохранены в формате JSON в файлах с расширением .info';
            }
            return parent::work($v);
        }
    }

    /**
     * Подготовка к экпорту
     * @param $objects Объекты для экпортирования
     * @return array
     */
    protected function exportInit($objects)
    {
        $info = array(
            'id' => 'export:'.uniqid(),
            'jobs' => array(),
            'jobs_step' => 0,
            'jobs_count' => 0
        );
        /** @var \Boolive\data\Entity $obj  */
        foreach ($objects as $obj){
            $info['jobs'][] = array(
                'count' => Data::select(array(
                    'select' => 'count',
                    'from' => array($obj),
                    'where'=> array('attr', 'is_delete', '>=', 0)
                )),
                'step' => 0,
                'obj' => $obj->id()
            );
            $info['jobs_count']++;
        }

        Session::set($info['id'], $info);
        return array(
            'id' => $info['id']
        );
    }
    /**
     * Выполнение шага экпортирования
     * @param string $id Идентификатор задачи экспортирования
     * @return array
     */
    protected function exportDo($id)
    {
        // Если есть сессия, задачи и текущая
        if (($info = Session::get($id)) && !empty($info['jobs']) && $info['jobs_step'] < $info['jobs_count']){
            $j = $info['jobs_step'];
            $message = '';
            if ($info['jobs'][$j]['step'] < $info['jobs'][$j]['count']){
                $cnt = 50;
                // Выбор объектов начиная со step
                $list = Data::select(array(
                    'from' => array($info['jobs'][$j]['obj']),
                    'where'=> array('attr', 'is_delete', '>=', 0),
                    'limit' => array($info['jobs'][$j]['step'], $cnt)
                ), null);
                if ($info['jobs'][$j]['step'] == 0){
                    $list[] = Data::read($info['jobs'][$j]['obj']);
                }

                foreach ($list as $obj){
                    if ($parent = $obj->parent()){
                        $childrens = $parent->exportedProperties();
                    }
                    // Если нет родителя, не указаны названия подчиненных или название объекта не в списке подчиненных
                    if (!$parent || empty($childrens) || (!empty($childrens) && is_array($childrens) && !in_array($obj->name(), $childrens))){
                        /** @var Entity $obj  */
                        $obj->export(true);
                        $message = $obj->uri();
                    }
                }
                // Экспортирование
                // Увеличение step
                $info['jobs'][$j]['step'] += $cnt;
            }
            if ($info['jobs'][$j]['step'] >= $info['jobs'][$j]['count']){
                $info['jobs_step'] = $j + 1;
            }

            // Процент полностью выполненных работ
            $progress = $info['jobs_step'] / $info['jobs_count'];
            // Добавляем процент неполностью выполненной работы
            if ($info['jobs_step'] < $info['jobs_count']){
                $progress += ($info['jobs'][$j]['step'] / $info['jobs'][$j]['count']) / $info['jobs_count'];
            }
            Session::set($info['id'], $info);
            $completed = $info['jobs_step'] == count($info['jobs']);
            return array(
                'id' => $id,
                'complete' => $completed,
                'progress' => round($progress * 100),
                'message' => $completed ? 'Завершено' : $message
            );
        }else{
            return array(
                'complete' => true,
                'progress' => 100,
                'message' => 'Нечего экспортировать'
            );
        }

//        if ($step == 0){
            // Подготовка процесса



            /** @var \Boolive\data\Entity $obj  */
//            foreach ($objects as $obj){
//                $info = array();
//                if ($obj->owner()) $info['owner'] = $obj->owner()->uri();
//                if ($obj->lang()) $info['lang'] = $obj->lang()->uri();
//                if ($obj->proto()) $info['proto'] = $obj->proto()->uri();
//                if (!$obj->isDefaultValue()){
//                    $info['value'] = $obj->value();
//                }else{
//                    $info['is_default_value'] = 1;
//                }
//                if ($obj->isDefaultChildren()) $info['is_default_children'] = 1;
//                if (!$obj->isDefaultClass()) $info['is_default_class'] = 0;
//                if ($obj->isFile()) $info['is_file'] = 1;
//                if ($obj->isHidden()) $info['is_hidden'] = 1;
//                if ($obj->isLink()) $info['is_link'] = 1;
//
//                $content = json_encode($info);
//                $file = $obj->dir(true).$obj->name().'.info';
//                \Boolive\file\File::create($content, $file);
//            }
//        }
    }
}
