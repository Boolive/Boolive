<?php
/**
 * Действие с прогрессом выполнения
 * Действие над объектами с подтверждением и прогрессом выполнения.
 * @version 1.0
 */
namespace Library\admin_widgets\ProgressAction;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class ProgressAction extends Widget
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity()),
                            Rule::entity()
                        )->required(),
                        'call' => Rule::string()->default('')->required(),
                        'id' => Rule::string()->default(0)->required() // Идентификатор действия для прогресса
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        // Экспорт
        if ($this->_input['REQUEST']['call'] == 'progress_start'){
            $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
            return $this->progressStart($objects);
        }else
        if ($this->_input['REQUEST']['call'] == 'progress'){
            return $this->progress($this->_input['REQUEST']['id']);
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
            $v['description'] = mb_split('\n', $this->description->value());
            if (sizeof($v['description'])<2) $v['description'][] = '';
            $v['submit_title'] = $this->submit_title->value();
            return parent::work($v);
        }
    }

    /**
     * Подготовка к прогрессу
     * @param $objects
     * @return array
     */
    protected function progressStart($objects)
    {
        $id = uniqid();
        return array('progress_id' => $id);
    }
    /**
     * Выполнение шага экпортирования
     * @param string $id Идентификатор задачи экспортирования
     * @return array
     */
    protected function progress($id)
    {
        return array(
            'complete' => true,
            'progress' => 100,
            'message' => 'Completed'
        );
    }
}