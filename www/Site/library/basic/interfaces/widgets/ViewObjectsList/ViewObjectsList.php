<?php
/**
 * Универсальный виджет списка
 *
 * Виджет для отображения списка любых объектов.
 * Объекты, которые нужно отобразить, указываются во входящих данных виджета в виде массива.
 * По uri каждого объекта или uri его прототипов осуществляется выбор одного из подчиенных виджетов,
 * которым осуществляется отображение объекта. Таким образом отображается каждый объект соответсвующим
 * для него видждетом.
 * Подчиенные виджеты группируются по услвоиям на объекты, для которых предначены. На один объект может
 * оказаться несколько подходящих виджетов. Автоматически выбирается первый.
 *
 * @version 1.0
 */
namespace library\basic\interfaces\widgets\ViewObjectsList;

use Boolive\values\Rule,
    library\basic\interfaces\widgets\Widget\Widget;

class ViewObjectsList extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'objects_list' => Rule::arrays(Rule::entity())->required(), // список объектов, которые отображать
                ), Rule::any() // не удалять другие элементы
            )), Rule::any() // не удалять другие элементы
        );
    }

    public function work($v = array())
    {
        $v['objects_list'] = array();
        // Все варианты отображений для последующего поиска нужного
        $options = $this->findAll(array(), false, 'value');
        $objects_list = $this->_input->GET->objects_list->get();

        foreach ($objects_list as $obj){
            $this->_input->GET->object = $obj;
            // Поиск варианта отображения для объекта
            while ($obj){
                $out = null;
                if (isset($options[$obj['uri']])){
                    $out = $options[$obj['uri']]->start($this->_commands, $this->_input);
                    if ($out){
                        $this->_input['previous'] = true;
                        $v['objects_list'][] = $out;
                    }
                }
                // Если виджеты не исполнялись, тогда ищем соответсвие по прототипу
                if (!$out){
                    $obj = $obj->proto();
                }else{
                    $obj = null;//чтоб остановить цикл
                }
            }
        }
        return parent::work($v);
    }
}
