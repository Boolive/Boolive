<?php
/**
 * Виджет формы обратной связи
 *
 * @version 1.0
 */
namespace Library\content_widgets\Feedback;

use Library\views\AutoWidgetList\AutoWidgetList,
    Boolive\values\Rule,
    Library\views\Widget\Widget,
    Boolive\input\Input;

class Feedback extends AutoWidgetList
{
    public function getInputRule()
    {
        return Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        // Модель формы
                        'object' => Rule::entity()->default($this->object)->required(),
                        // Признак, submit запрос данной формы?
                        $this['uri'] => Rule::arrays(array(
                                'submit' => Rule::string()
                            )
                        ),
                        // Признак успешности обработки формы
                        'ok' => Rule::eq(md5($this['uri']))->default(false)->required()
                    )
                )
            )
        );
    }

    public function work($v = array()){
        // Обработка объекта - формирование полей формы с проверкой введенных значений
        $list = $this->getList();
        $v['view'] = array();
        foreach ($list as $object){
            $this->_input_child['REQUEST']['object'] = $object;
            if ($result = $this->startChild('switch_views')){
                $v['view'][$object->getName()] = $result;
            }
        }
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];

        // Если у формы нажата кнопка SUBMIT
        if (isset($this->_input['REQUEST'][$this['uri']]['submit'])){
            //Выполнение действия (отправка)
            if ($this->_input_child['REQUEST']['object']->send()){
                // Редирект на адрес текущего запроса с параметром ok
                $this->_commands->redirect(Input::url(null, 0, array('ok'=>md5($this['uri'])), true, true));
            }else{
                $v['error'] = true;
                $v['error_message'] = $this->_input_child['REQUEST']['object']->error_message->getValue();
            }
            $this->_input['REQUEST']['ok'] = false;
        }
        if ($v['ok'] = (bool)$this->_input['REQUEST']['ok']){
            $v['result_message'] = $this->_input_child['REQUEST']['object']->result_message->getValue();
        }
        // Отображение формы
        $v['uri'] = $this->_input['REQUEST']['object']['uri'];
        return Widget::work($v);
    }
}