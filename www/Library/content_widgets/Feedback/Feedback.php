<?php
/**
 * Виджет формы обратной связи
 *
 * @version 1.0
 */
namespace Library\content_widgets\Feedback;

use Library\views\AutoWidgetList\AutoWidgetList;

class Feedback extends AutoWidgetList
{
//    public function getInputRule()
//    {
//        return Rule::arrays(array(
//            'GET' => Rule::arrays(array(
//                'object' => Rule::entity(), // объект, который отображать
//                ), Rule::any()
//            ),
//            'POST' => Rule::arrays(array(
//                'object' => Rule::entity(), // объект, который редактировать
//                ), Rule::any()
//            )
//        ), Rule::any());
//    }
//
//    public function canWork()
//    {
//        if ($result = parent::canWork()){
//            if (isset($this->_input['POST']['object'])){
//                $this->_input['POST']['objects_list'] = $this->_input['POST']['object']->findAll(array(
//                    'order' =>'`order` ASC'
//                ), true);
//            }else
//            if (isset($this->_input['GET']['object'])){
//                $this->_input['GET']['objects_list'] = $this->_input['GET']['object']->findAll(array(
//                    'order' =>'`order` ASC'
//                ));
//            }else{
//                $result = false;
//            }
//        }
//        return $result;
//    }
//
//    public function work($v = array()){
//        if (isset($this->_input['POST']['object'])){
//            // На обработку
//            //echo '<h2>Сообщение отправлено</h2>';
//            //$result = parent::work($v);
//            // Что-то делаем с объектом, например сохраняем
//
//            // Редирект нужен?
//            trace($this->_input['POST']['object']);
//            //return $result;
//        }else{
//            $v['obj'] = $this->_input['GET']['object']['uri'];
//            $v['uri'] = $this['uri'];
//            return parent::work($v);
//        }
//    }
    public function work($v = array()){
        return parent::work($v);
    }
}