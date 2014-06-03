<?php
/**
 * Удалить навсегда
 * Отображает диалоговое окно для подтверждения удаления и осуществляет
 * удаление навсегда (уничтожает) выбранных объектов и их подчиенных
 * @version 1.0
 */
namespace site\library\admin_widgets\Destroy;

use boolive\data\Data,
    boolive\errors\Error,
    boolive\file\File,
    site\library\views\Widget\Widget,
    boolive\values\Rule;
use boolive\data\Entity;

class Destroy extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::any(
                    Rule::arrays(Rule::entity(array('access', 'destroy')/*array('attr','is_draft','=',1)*/)),
                    Rule::entity(array('access', 'destroy')/*array('attr','is_draft','=',1)*/)
                )->required(),
                'call' => Rule::string()->default('')->required(),
                'select' => Rule::in(null, 'structure', 'property', 'heirs')->required()
            ))
        ));
    }


    function show($v = array(), $commands, $input)
    {
        // Удаление
        if ($this->_input['REQUEST']['call'] == 'destroy'){
            $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
            foreach ($objects as $o){
                /** @var \boolive\data\Entity $o */
                // Уничтожение с проверкой доступа и целостностью данных
                try{
                    if ($this->_input['REQUEST']['select'] == 'heirs'){
                        $this->deleteHeirs($o);
                    }else{
                        $o->destroy(true, true);
                    }
                }catch (Error $e){
                    if (empty($v['error'])) $v['error'] = '';
                    $v['error'].= $e->getUserMessage(true);
                }
            }
            $v['result'] = true;
            return $v;
        }
        // Отображение
        else{
            $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
            $v['data-o'] = array();
            $v['objects'] = array();
            $v['conflicts'] = array();
            foreach ($objects as $o){
                $item = array();
                if (!($item['title'] = $o->title->value())){
                    $item['title'] = $o->name();
                }
                $item['uri'] = $o->uri();
                $v['objects'][] = $item;
                $v['data-o'][]=$o->uri();
//                $conflits = Data::deleteConflicts($o, true, true);
//                $v['conflicts'] = array_merge_recursive($v['conflicts'], $conflits);
            }
            $v['data-o'] = json_encode($v['data-o']);
            $v['title'] = $this->title->value();
            if (count($objects)>1){
                $v['question'] = 'Вы действительно желаете уничтожить эти объекты?';
                $v['message'] = 'Объекты и их подчинённые будут удалены навсегда, их нельзя будет восстановить.';
            }else{
                $v['question'] = 'Вы действительно желаете уничтожить этот объект?';
                $v['message'] = 'Объект и его подчинённые будут удалены навсегда, их нельзя будет восстановить.';
            }
            $v['prev'] = '';//$this->_input['REQUEST']['prev']? $this->_input['REQUEST']['prev']->uri() : '';

            // Конфликты для уничтожения


            return parent::show($v, $commands, $input);
        }
    }

    function deleteHeirs($obj)
    {
        $heirs = $obj->find(array('select'=>'heirs', 'depth'=>array(1,1), 'where'=>array(
            array('attr', 'is_hidden', '>=', 0),
            array('attr', 'is_draft', '>=', 0),
            array('attr', 'is_mandatory', '>=', 0)
        )));
        foreach ($heirs as $h) $this->deleteHeirs($h);
        $obj->destroy(true, true);
    }

}