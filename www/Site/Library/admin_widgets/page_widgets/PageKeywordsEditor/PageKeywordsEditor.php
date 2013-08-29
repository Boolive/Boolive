<?php
/**
 * Виджет редактирования ключевых слов страницы
 * @author: polinа Putrolaynen
 * @version 1.0
 */
namespace Library\admin_widgets\page_widgets\PageKeywordsEditor;

use Boolive\data\Data;
use Boolive\functions\F;
use Library\views\AutoWidgetList\AutoWidgetList,
    Boolive\values\Rule;

class PageKeywordsEditor extends AutoWidgetList
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity()->required(),
                'call' => Rule::string()->default('')->required(),
                'request' => Rule::string()->default('')->required(),
                'Keyword' => Rule::arrays(Rule::string()),
                'saveOrder' => Rule::arrays(Rule::arrays(Rule::string()))
            ))
        ));
    }
    public function work($v = array())
    {
        // Сохранение атрибутов
        if ($this->_input['REQUEST']['call'] == 'save'){
            return $this->callSave();
        }
        if ($this->_input['REQUEST']['call'] == 'find'){
            return $this->callFind();
        }
        //Изменение порядка элемента при сортировке drag-and-drop
        if (isset($this->_input['REQUEST']['saveOrder']) && $this->_input['REQUEST']['call'] == 'saveOrder'){
            return $this->callSaveOrder(
                $this->_input['REQUEST']['saveOrder']['objectUri'],
                $this->_input['REQUEST']['saveOrder']['nextUri']
            );
        }
        $v['object'] = $this->_input['REQUEST']['object']->uri();
        $v['value'] = $this->_input['REQUEST']['object']->value();
        $v['title'] = $this->_input['REQUEST']['object']->title->value();
        return parent::work($v);
    }

    /**
     * Сохранение атрибутов объекта
     * @return mixed
     */
    protected function callSave()
    {
        $v = array();
        $obj = $this->_input['REQUEST']['object'];
        $key_title = $this->_input['REQUEST']['Keyword']['value'];
        $key_name = F::translit($key_title);
        $keywords = Data::read('/Keywords');
        $key = $keywords->{$key_name};
        // Создание слова в общей коллекции ключевых слов
        if (!$key->isExist()){
            $proto = Data::read('/Library/content_samples/Keyword');
            $key = $proto->birth($keywords);
            $key->name($key_name);
            $key->value(0);
            $key->save();
            $key->title->value($key_title);
            $key->title->save();
        }
        // Ключевое слово в локальном списке (например, в странице)
        $key_local = $obj->{$key_name};
        // Добавление слова по ссылке
        if (!$key_local->isExist()){
            $key_local = $key->birth($obj);
            $key_local->isLink(true);
            $key_local->save();
            // Счётчик использования слова
            $key->value($key->value()+1);
            $key->save();
        }else
        if ($key_local->isDelete()){
            $key_local->isDelete(false);
            $key_local->save();
            // Счётчик использования слова
            $key->value($key->value()+1);
            $key->save();
        }else{
            return false;
        }
        return $key_local->uri();
    }

    /**
     * Устанавливает новый порядок объектов
     */
    protected function callSaveOrder($object, $next)
    {
        $obj = Data::read($object['uri']);
        if (!empty($next)){
            $next_object = Data::read($next['uri']);
            if ($next_object->isExist()){
                if ($next['next'] > 0){
                    $obj->order($next_object->order());
                } else{
                    $obj->order($next_object->order() + 1);
                }
            }
        }
        $obj->save();
        return true;
    }

    /*
     * Ищет ключевые слова по символам, введенным пользователем.
     */
    protected function callFind()
    {
        $keywords = Data::read('/Keywords');
        $result = $keywords->find(array(
            'where' => array(
                array("attr", "name", "like", $this->_input['REQUEST']['request'] . "%")
            )
        ));
        $suggetions = array();
        foreach ($result as $item){
            $suggetions[] = $item->name();
        }
        return $suggetions;
    }
}