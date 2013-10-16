<?php
/**
 * Отображение объекта в виде пункта списка
 *
 * @version 1.0
 */
namespace Library\admin_widgets\Explorer\views\ObjectItem;

use Library\views\Widget\Widget,
    Boolive\values\Rule;

class ObjectItem extends Widget
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        'object' => Rule::entity($this->object_rule->value())->required(),
                        'view_kind' => Rule::eq('list')
                    )
                )
            )
        );
    }

    public function work($v = array())
    {
        /** @var $obj \Boolive\data\Entity */
        $obj = $this->_input['REQUEST']['object'];
        // Заголовк объекта
        $v['title'] = $obj->linked()->title->value();

        if (empty($v['title'])){
            $v['title'] = $obj->name();
        }
        // Описание объекта
        if ($obj->description->isExist()){
            $v['description'] = $obj->description->value();
        }else
        if ($obj->isLink()){
            $v['description'] = $obj->linked()->description->value();
        }else{
            $v['description'] = '';
        }
        // Атрибуты
        $v['name'] = $obj->name();
        $v['value'] = (string)$obj->value();
        $v['uri'] = $obj->uri(false, true);
        $v['is_hidden'] = $obj->isHidden(null, false);
        $v['is_delete'] = $obj->isDelete(null, false);
        $v['is_file'] = $obj->isFile();
        $v['is_link'] = $obj->isLink();
        $v['is_default_value'] = $obj->isDefaultValue();
        $v['link'] = $obj->linked()->uri(false, true);
        if ($v['is_link']){
            $v['alt'] = 'Ссылка: '.$obj->linked()->uri();
        }else{
            $v['alt'] = 'Объект: '.$obj->linked()->uri();
        }
        $v['value_full'] = $obj->value();
        $v['value'] = mb_substr($v['value_full'], 0, 50);
        if ($v['value']!=$v['value_full']){
            $v['value'].='...';
        }
        $v['diff'] = $obj->diff();
        return parent::work($v);
    }
}