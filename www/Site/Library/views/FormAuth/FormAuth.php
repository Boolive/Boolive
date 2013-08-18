<?php
/**
 * Форма аутентификации пользователя
 *
 * @version 1.0
 */
namespace Library\views\FormAuth;

use Library\views\AutoWidgetList\AutoWidgetList,
    Library\views\Widget\Widget,
    Boolive\input\Input,
    Boolive\values\Rule;

class FormAuth extends AutoWidgetList
{
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'REQUEST' => Rule::arrays(array(
                        // Модель формы
                        'object' => Rule::entity()->required(),
                        // Признак, submit запрос данной формы?
                        $this->id() => Rule::arrays(array(
                                'submit' => Rule::string()
                            )
                        ),
                        // Признак успешности обработки формы
                        'ok' => Rule::eq(md5($this->id()))->default(false)->required()
                    )
                ),
                'PATH' => Rule::arrays(array(
                    0 => Rule::any(Rule::eq('admin'), Rule::eq('login'))->required()
                    )
                )
            )
        );
    }

    public function work($v = array()){
//        trace($this->_input['REQUEST']['object']);
//        $this->_input['REQUEST']['object'] = $this->object;
        // Обработка объекта - формирование полей формы с проверкой введенных значений
        $list = $this->getList();
        $v['view'] = array();
        foreach ($list as $object){
            $this->_input_child['REQUEST']['object'] = $object;
            if ($result = $this->startChild('switch_views')){
                $v['view'][$object->name()] = $result;
            }
        }
        $this->_input_child['REQUEST']['object'] = $this->_input['REQUEST']['object'];

        // Если у формы нажата кнопка SUBMIT
        if (isset($this->_input['REQUEST'][$this->id()]['submit'])){
            //Выполнение действия (отправка)
            if ($this->_input['REQUEST']['object']->auth()){
                $this->_commands->redirect(Input::url(null, 0, array('ok'=>md5($this->id())), true, true));
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
        return Widget::work($v);
    }
}