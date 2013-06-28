<?php
/**
 * Удалить навсегда
 * Отображает диалоговое окно для подтверждения удаления и осуществляет
 * удаление навсегда (уничтожает) выбранных объектов и их подчиенных
 * @version 1.0
 */
namespace Library\admin_widgets\Destroy;

use Boolive\data\Data;
use Boolive\file\File;
use Library\views\Widget\Widget,
    Boolive\values\Rule;

class Destroy extends Widget
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity(/*array('attr','is_delete','=',1)*/)),
                            Rule::entity(/*array('attr','is_delete','=',1)*/)
                        )->required(),
//                        'prev' => Rule::entity(),
                        'call' => Rule::string()->default('')->required(),
                    )
                )
            )
        );
    }


    public function work($v = array())
    {
        // Удаление
        if ($this->_input['REQUEST']['call'] == 'destroy'){
            $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);
            foreach ($objects as $o){
                /** @var \Boolive\data\Entity $o */
                // Уничтожение с проверкой доступа и целостностью данных
                $o->destroy($error, true, true);
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
                $v['data-o'][]=$item['uri'];
                $conflits = Data::deleteConflicts($o, true, true);
                $v['conflicts'] = array_merge_recursive($v['conflicts'], $conflits);
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


            return parent::work($v);
        }
    }
}
