<?php
/**
 * Установщик
 * Установка новых объектов и обновлений
 * @version 1.0
 */
namespace site\library\admin\widgets\Installer;

use boolive\data\Data2,
    boolive\data\Entity,
    site\library\views\Widget\Widget,
    boolive\values\Rule;

class Installer extends Widget
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::any(
                )->required(),
                'call' => Rule::string()->default('')->required(),
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        // Установка объекта
        if ($this->_input['REQUEST']['call'] == 'install'){
            Data2::applyUpdates($this->_input['REQUEST']['object']);
            return true;
        }
        // Отображение
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
            $v['question'] = 'Вы действительно желаете установить эти объекты?';
            $v['message'] = '';
        }else{
            $v['question'] = 'Вы действительно желаете установить этот объект?';
            $v['message'] = '';
        }
        return parent::show($v, $commands, $input);
    }
}