<?php
/**
 * Виджет формы обратной связи
 *
 * @version 1.0
 */
namespace Library\content_widgets\Feedback;

use Library\views\AutoWidgetList2\AutoWidgetList2,
    Boolive\values\Rule,
    Library\views\Widget\Widget,
    Boolive\input\Input;

class Feedback extends AutoWidgetList2
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                // Модель формы
                'object' => Rule::entity($this->object_rule->value())->required(),
                // Признак, submit запрос данной формы?
                $this->uri() => Rule::arrays(array(
                    'submit' => Rule::string()
                )),
                // Признак успешности обработки формы
                'ok' => Rule::eq(md5($this->uri()))->default(false)->required()
            ))
        ));
    }

    function show($v = array(), $commands, $input){
        // Обработка объекта - формирование полей формы с проверкой введенных значений
        $list = $this->getList();
        $v['views'] = array();
        foreach ($list as $object){
            $this->_input_child['REQUEST']['object'] = $object;
            if ($result = $this->startChild('views')){
                $v['views'][$object->name()] = $result;
            }
        }
        unset($object);
        $object = $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];

        // Если у формы нажата кнопка SUBMIT
        if (isset($this->_input['REQUEST'][$this->uri()]['submit'])){
            //Выполнение действия (отправка)
            if ($object->send()){
                // Редирект на адрес текущего запроса с параметром ok
                $this->_commands->redirect(Input::url(null, 0, array('ok'=>md5($this->uri())), true, true));
            }else{
                $v['error'] = true;
                $v['error_message'] = $object->error_message->value();
            }
            $this->_input['REQUEST']['ok'] = false;
        }
        if ($v['ok'] = (bool)$this->_input['REQUEST']['ok']){
            $v['result_message'] = $object->result_message->value();
        }
        // Отображение формы
        $v['uri'] = $object->uri();
        return Widget::show($v, $commands, $input);
    }
}