<?php
/**
 * Виджет редактирования ключевых слов страницы
 * @author: polinа Putrolaynen
 * @version 1.0
 */
namespace Site\library\admin_widgets\Editor\views\KeywordsField;

use Boolive\data\Data;
use Boolive\functions\F;
use Site\library\views\AutoWidgetList2\AutoWidgetList2,
    Boolive\values\Rule;

class KeywordsField extends AutoWidgetList2
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::entity($this->object_rule->value())->required(),
                //'path' => Rule::regexp($this->path_rule->value())->required(),
                'call' => Rule::string()->default('')->required(),
                'request' => Rule::string()->default('')->required(),
                'Keyword' => Rule::arrays(Rule::string()),
                'saveOrder' => Rule::arrays(Rule::arrays(Rule::string()))
            ))
        ));
    }

    function show($v = array(), $commands, $input)
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
        /** @var $obj \Boolive\data\Entity */
        $obj = $this->_input['REQUEST']['object'];
        // Заголовк и описание объекта
        $v['title'] = $obj->title->inner()->value();
        if (empty($v['title'])) $v['title'] = $obj->name();
        $v['description'] = $obj->description->inner()->value();

        // Атрибуты
        $v['name'] = $obj->name();
        $v['value'] = (string)$obj->value();
        $v['uri'] = $obj->uri(false, true);
        $v['is_hidden'] = $obj->isHidden(null, false);
        $v['is_draft'] = $obj->isDraft(null, false);
        $v['is_file'] = $obj->isFile();
        $v['is_link'] = $obj->isLink();
        $v['is_mandatory'] = $obj->isMandatory();
        $v['is_default_value'] = $obj->isDefaultValue();
        $v['link'] = $obj->linked()->uri(false, true);
        if ($p = $obj->proto()){
            $v['newlink'] = $obj->proto()->linked()->uri();
        }else{
            $v['newlink'] = Entity::ENTITY_ID;
        }
        $v['value'] = $obj->value();
        $v['value_short'] = mb_substr($v['value'], 0, 50);
        if ($v['value_short'] != $v['value']){
            $v['value_short'].= '...';
        }
        return parent::show($v, $commands, $input);
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
        $key_name = mb_strtolower(F::translit($key_title));
        $keywords = Data::read('/contents/Keywords');
        $key = $keywords->{$key_name};
        // Создание слова в общей коллекции ключевых слов
        if (!$key->isExist()){
            $proto = Data::read('/library/content_samples/Keyword');
            $key = $proto->birth($keywords, false);
            $key->name($key_name);
            $key->value(0);
            $key->title->value($key_title);
            $key->save();
        }
        // Ключевое слово в локальном списке (например, в странице)
        $key_local = $obj->{$key_name};
        // Добавление слова по ссылке
        if (!$key_local->isExist()){
            $key_local = $key->birth($obj, false);
            $key_local->isLink(true);
            $key_local->isDraft(false);
            $key_local->save();
            // Счётчик использования слова
            $key->value($key->value()+1);
            $key->save();
        }else
        if ($key_local->isDraft()){
            $key_local->isDraft(false);
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
        $keywords = Data::read('/contents/Keywords');
        $result = $keywords->find(array(
            'where' => array(
                array("attr", "name", "like", $this->_input['REQUEST']['request'] . "%"),
                array('attr', 'is_hidden', '<=', $keywords->_attribs['is_hidden'])
            )
        ));
        $suggetions = array();
        foreach ($result as $item){
            $suggetions[] = $item->name();
        }
        return $suggetions;
    }
}