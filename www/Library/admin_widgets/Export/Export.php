<?php
/**
 * Экспорт
 * Сохраняет выбранные объекты в файловую систему
 * @version 1.0
 */
namespace Library\admin_widgets\Export;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class Export extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::any(
                            Rule::arrays(Rule::entity(array('attr','is_delete','=',0))),
                            Rule::entity(array('attr','is_delete','=',0))
                        )->required(),
                        'call' => Rule::string()->default('')->required(),
                    )
                )
            )
        );
    }


    public function work($v = array())
    {
        // Удаление
        if ($this->_input['REQUEST']['call'] == 'start'){
            $objects = is_array($this->_input['REQUEST']['object'])? $this->_input['REQUEST']['object'] : array($this->_input['REQUEST']['object']);

            $v['result'] = true;
            return $v;
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
                $v['message'] = 'Объекты и его подчинённые будут сохранены в файлы .info в своих папках';
            }else{
                $v['message'] = 'Объект и его подчинённые (свойства) будут сохранены в файлы .info в своих папках';
            }
            return parent::work($v);
        }
    }
}
