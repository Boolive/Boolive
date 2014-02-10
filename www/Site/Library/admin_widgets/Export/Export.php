<?php
/**
 * Экспорт
 * Сохраняет выбранные объекты в файловую систему
 * @version 1.0
 */
namespace Site\Library\admin_widgets\Export;

use Boolive\data\Data;
use Boolive\data\Entity;
use Boolive\develop\Trace;
use Boolive\file\File;
use Boolive\session\Session;
use Site\Library\views\Widget\Widget,
    Boolive\values\Rule;

class Export extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::any(
                    Rule::arrays(Rule::entity()),
                    Rule::entity()
                )->required(),
                'call' => Rule::string()->default('')->required(),
                'id' => Rule::string()->default(0)->required(),
                'select' => Rule::in(null, 'structure', 'property')
            ))
        ));
    }

    function show($v = array(), $commands, $input)
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
                if (!($item['title'] = $o->title->inner()->value())){
                    $item['title'] = $o->name();
                }
                $item['uri'] = $o->uri();
                $v['objects'][] = $item;
                $v['data-o'][]=$item['uri'];
            }
            $v['data-o'] = json_encode($v['data-o']);
            $v['title'] = $this->title->inner()->value();
            if (count($objects)>1){
                $v['question'] = 'Вы действительно желаете экспортировать эти объекты?';
                $v['message'] = 'Объекты и их подчинённые будут сохранены в файлы .info';
            }else{
                $v['question'] = 'Вы действительно желаете экспортировать этот объект?';
                $v['message'] = 'Объект и его подчинённые будут сохранены в формате JSON в файлах с расширением .info';
            }
            return parent::show($v, $commands, $input);
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
                'count' => Data::read(array(
                    'select' => array('count', 'children'),
                    'from' => $obj,
                    'depth' => 'max',
                    'where'=> array(
                        array('attr', 'is_draft', '>=', 0),
                        array('attr', 'is_hidden', '>=', 0),
                        array('attr', 'is_property', '=', 0)
                    )
                ))+1,
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
            if ($info['jobs'][$j]['step'] <= $info['jobs'][$j]['count']){
                $cnt = 20;
                // Выбор объектов начиная со step
                $list = Data::read(array(
                    'select' => array('children'),
                    'from' => $info['jobs'][$j]['obj'],
                    'depth' => 'max',
                    'where'=> array(
                        array('attr', 'is_draft', '>=', 0),
                        array('attr', 'is_hidden', '>=', 0),
                        array('attr', 'is_property', '=', 0)
                    ),
                    'order'=> array(array('id', 'asc')),
                    'limit' => array($info['jobs'][$j]['step'], $cnt)
                ));
                if ($info['jobs'][$j]['step'] == 0){
                    $root =  Data::read($info['jobs'][$j]['obj']);
                    if (!$root->isProperty()){
                        $list[] = $root;
                    }
                }

                foreach ($list as $obj){
                    $obj->export(true);
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
                'message' => $completed ? ' Завершено' : $message
            );
        }else{
            return array(
                'complete' => true,
                'progress' => 100,
                'message' => 'Нечего экспортировать'
            );
        }
    }
}