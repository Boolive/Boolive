<?php
/**
 * Виджет формы обратной связи
 *
 * @version 1.0
 */
namespace Library\content_widgets\Feedback;

use Library\basic\widgets\ViewObjectsList\ViewObjectsList,
    Boolive\values\Rule,
    Boolive\commands\Commands;

class Feedback extends ViewObjectsList
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'object' => Rule::entity()->required(), // объект, который отображать
                ), Rule::any() // не удалять другие элементы
            )), Rule::any() // не удалять другие элементы
        );
    }

    public function canWork()
    {
        if ($result = parent::canWork()){
            // Выбираем всех подчиненных страницы
            $this->_input['GET']['objects_list'] = $this->_input['GET']['object']->findAll(array(
                'order' =>'`order` ASC'
            ));
        }
        return $result;
    }

    public function work($v = array()){

        $v['uri'] = $this['uri'];
        return parent::work($v);
    }

    /**
     * Обработка формы
     * @param Commands $commands
     * @param $input
     * @return void
     */
    public function process(Commands $commands, $input){
        trace($input);
        //$commands->redirect('/main');
    }
}