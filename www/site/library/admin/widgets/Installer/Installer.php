<?php
/**
 * Установщик
 * Установка новых объектов и обновлений
 * @version 1.0
 */
namespace site\library\admin\widgets\Installer;

use boolive\data\Data,
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
//                    Rule::arrays(Rule::entity(array('attr', 'diff', '!=', Entity::DIFF_NO))),
//                    Rule::entity(array('attr', 'diff', '!=', Entity::DIFF_NO))
                )->required(),
                'call' => Rule::string()->default('')->required(),
            ))
        ));
    }

    function show($v = array(), $commands, $input)
    {
        // Установка объекта
        if ($this->_input['REQUEST']['call'] == 'install'){
            Data::applyUpdates($this->_input['REQUEST']['object']);
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
//            $item['diff'] = $o->diff();
//            switch ($item['diff']){
//                case Entity::DIFF_CHANGE:
//                    $item['diff_message'] = 'Изменен ';
//                    break;
//                case Entity::DIFF_ADD:
//                    $item['diff_message'] = 'Добавлен ';
//                    break;
//                case Entity::DIFF_DELETE:
//                    $item['diff_message'] = 'Уничтожен ';
//                    break;
//            }
//            if ($o->diff_from() == 1){
//                $item['diff_message'].=' в прототипе';
//            }else{
//                $item['diff_message'].=' в файле';
//                if ($o->diff_from()<0){
//                    $item['diff_message'].=' родителя';
//                }
//            }
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