<?php
/**
 * Форма аутентификации пользователя
 *
 * @version 1.0
 */
namespace Site\library\forms\FormAuth;

use Site\library\views\AutoWidgetList2\AutoWidgetList2,
    Site\library\views\Widget\Widget,
    Boolive\input\Input,
    Boolive\values\Rule;

class FormAuth extends AutoWidgetList2
{
    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                // Модель формы
                'object' => Rule::entity()->required(),
                // Признак, submit запрос данной формы?
                $this->uri() => Rule::arrays(array(
                    'submit' => Rule::string()
                )),
                // Признак успешности обработки формы
                'ok' => Rule::eq(md5($this->uri()))->default(false)->required(),
                'path' => Rule::regexp($this->path_rule->value())->required(),
            )),
            'previous' => Rule::eq(false)
        ));
    }

    function show($v = array(), $commands, $input){
        if (isset($this->_input_child['REQUEST']['view_name'])){
            unset($this->_input_child['REQUEST']['view_name']);
        }
//        trace($this->_input['REQUEST']['object']);
//        $this->_input['REQUEST']['object'] = $this->object;
        // Обработка объекта - формирование полей формы с проверкой введенных значений
        $list = $this->getList();
        $v['views'] = array();
        foreach ($list as $object){
            $this->_input_child['REQUEST']['object'] = $object;
            if ($result = $this->startChild('views')){
                $v['views'][$object->name()] = $result;
            }
        }
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];

        // Если у формы нажата кнопка SUBMIT
        if (isset($this->_input['REQUEST'][$this->uri()]['submit'])){
            //Выполнение действия (отправка)
            if ($this->_input['REQUEST']['object']->auth()){
                $this->_commands->redirect(Input::url(null, 0, array('ok'=>md5($this->uri()))));
            }else{
                $v['error'] = $this->_input_child['REQUEST']['object']->error_message->value();
            }
            $this->_input['REQUEST']['ok'] = false;
        }
        if ($this->_input['REQUEST']['ok']){
            $v['ok'] =  $this->_input['REQUEST']['object']->ok_message->value();
        }
        // Отображение формы
        $v['uri'] = $this->_input['REQUEST']['object']->id();
        return Widget::show($v, $commands, $input);
    }
}