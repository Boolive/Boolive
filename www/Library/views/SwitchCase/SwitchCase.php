<?php
/**
 * Вариант переключателя
 *
 * Используется в виджете-переключателе для автоматического выбора варианта по uri отображаемого объекта.
 * В качестве значения содержит условие исполнения, но сам его не проверяет и не контролирует,
 * так как значение является условием выбора вариант в родителе. В родителе сверяются значения и запускается вариант.
 * Сам вариант исполняет по очереди свои подчиненные объекты, пока один из них не сработает, таким образом
 * автоматически исполнится не больше одного подчиненного объекта-вида.
 * Если во входящих данных указано имя подчиненного объекта-вида, то исполняется именно он.
 *
 * @version 1.0
 */
namespace Library\views\SwitchCase;

use Boolive\values\Rule,
    Library\views\View\View;

class SwitchCase extends View
{
    protected $_views;

    public function getInputRule()
    {
        return Rule::arrays(array(
                'GET' => Rule::arrays(array(
                        'object' => Rule::entity()->default(null)->required(),
                        'view' => Rule::string()->default('')->required(), // имя виджета, которым отображать принудительно
                    )
                )
            )
        );
    }

    protected function initInputChild($input)
    {
        parent::initInputChild(array_replace_recursive($input, $this->_input));
    }

    public function work($v = array())
    {
        // Запускаем по очереди подчиненных варианта, пока один из них не сработает
        if ($this->_input['GET']['view']){
            // Если указано, каким отображать, то только его пробуем запустить
            $views = array($this->{$this->_input['GET']['view']});
        }else{
            // Все виджеты варианта
            $views = $this->getViews();
        }
        $view = reset($views);
        while ($view){
            $v['view'] = $view->start($this->_commands, $this->_input_child);
            if ($v['view'] != null){
                $this->_input_child['previous'] = true;
                return $v['view'];
            }
            $view = next($views);
        }
        return null;
    }

    protected function getViews(){
        if (!isset($this->_views)){
            $this->_views = $this->findAll(array('where' => 'is_history=0 and is_delete=0', 'order'=>'`order` ASC'));
            unset($this->_views['title'], $this->_views['description']);
        }
        return $this->_views;
    }
}