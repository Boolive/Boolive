<?php
/**
 * Виджет редактирования ключевых слов страницы
 * @author: polinа Putrolaynen
 * @version 1.0
 */
namespace Library\admin_widgets\page_widgets\PageKeywordsEditor;

use Boolive\data\Data;
use Library\views\AutoWidgetList\AutoWidgetList,
    Boolive\values\Rule;

class PageKeywordsEditor extends AutoWidgetList
{
    public function getInputRule()
      {
          return Rule::arrays(array(
                 'REQUEST' => Rule::arrays(array(
                         'object' => Rule::entity()->required(),
                         'call' => Rule::string()->default('')->required(),
                         'request' => Rule::string()->default('')->required(),
                         'Keyword' => Rule::arrays(Rule::string()),
                         'saveOrder' => Rule::arrays(Rule::arrays(Rule::string()))
                         )
                      )
                   )
               );
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
          $new_keyword = $this->_input['REQUEST']['Keyword']['value'];
          $key = Data::read('/Keywords/'.$new_keyword);
          if($key->isExist()){
              //Есть ли это слово у страницы уже
            $existing = $obj->find(array(
                         'where' => array(
                             array("attr","name", '=',$key->name()),
                             array("attr","is_delete", '>=',0)
                         )
                      ));
              if(empty($existing)){
                $obj->add($key);
                $obj->save();
                return $obj->{$key->name()}->uri();
              }else{
                  $existing[$key->name()]->isDelete(false);
                  $existing[$key->name()]->save();
                  return $existing[$key->name()]->uri();
              }
          }else{
             $key = Data::read('/Library/content_samples/Keyword');
             $keywords = Data::read('/Keywords');
             $keywords->{$new_keyword} = $key->birth();
             $keywords->save();
             $obj->{$new_keyword} = $keywords->{$new_keyword}->birth();
             $obj->save();
             return $obj->{$new_keyword}->uri();
          }

       }

    /**
    * Устанавливает новый порядок объектов
    */
   protected function callSaveOrder($object, $next){
       $obj = Data::read($object['uri']);
       if (!empty($next)) {
           $next_object =Data::read($next['uri']);
           if ($next_object->isExist()) {
               if ($next['next'] > 0) {
                   $obj->order($next_object->order());
               } else {
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
    protected function callFind(){
        $keywords = Data::read('/Keywords');
       $result =  $keywords->find(array(
           'where' => array(
               array("attr","name","like",$this->_input['REQUEST']['request']."%")
           )
        ));
        $suggetions = array();
        foreach($result as $item){
            $suggetions[] = $item->name();
        }
        return $suggetions;
    }
}