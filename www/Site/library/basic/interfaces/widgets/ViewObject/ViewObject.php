<?php
/**
 * Универсальный виджет
 *
 * Виджет для отображения любого объекта.
 * Объект, который нужно отобразить, указывается во входящих данных виджета.
 * По uri объекта или uri его прототипов осуществляется выбор одного из подчиенных виджетов,
 * которым уже будет осуществлено отображение объекта.
 * Подчиенные виджеты группируются по услвоиям на объекты, для которых предначены. На один объект может
 * оказаться несколько подходящих виджетов. Автоматически выбирается первый.
 * Если во входящих данных указано имя виджета, то используется именно он для отображения.
 *
 * @version 1.0
 */
namespace library\basic\interfaces\widgets\ViewObject;

use Boolive\values\Rule,
    library\basic\interfaces\widgets\Widget\Widget;

class ViewObject extends Widget
{
    public function getInputRule(){
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // объект, который отображать
                'view' => Rule::string() // имя виджета, которым отображать принудительно
                ), Rule::any() // не удалять другие элементы
            )), Rule::any() // не удалять другие элементы
        );
    }

    public function work($v = array()){
        // Все варианты отображений для последующего поиска нужного
        $views = $this->findAll(array(), false, 'value');
        $view_name = $this->_input->GET->view->string();
        $obj = $this->_input->GET->object->get();
        $v['object'] = null;
        // Поиск варианта отображения для объекта
        while ($obj){
            if (isset($views[$obj['uri']])){
                // Запускаем по очереди виджеты варианта, пока один из виджетов не сработает
                if ($view_name){
                    // Если указано, каким отображать, то только его пробуем запустить
                    $widgets = array($views[$obj['uri']]->{$view_name});
                }else{
                    // Все виджеты варианта
                    $widgets = $views[$obj['uri']]->findAll(array('order'=>'`order` ASC'));
                }
                $wg = reset($widgets);
                while ($wg){
                    $v['object'] = $wg->start($this->_commands, $this->_input);
                    if ($v['object'] != null){
                        $this->_input['previous'] = true;
                        $wg = null;//чтоб оставновить цикл
                    }else{
                        $wg = next($widgets);
                    }
                }
            }
            // Если виджеты не исполнялись, тогда ищем соответсвие по прототипу
            if ($v['object'] == null){
                $obj = $obj->proto();
            }else{
                $obj = null;//чтоб остановить цикл
            }
        }
        return parent::work($v);
    }
}