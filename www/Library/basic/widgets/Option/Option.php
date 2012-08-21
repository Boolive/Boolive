<?php
/**
 * Вариант отображения
 *
 * Используется для организации вариантов отображений родительского виджета.
 * Объект-вариант в качестве значения содержит условие исполнения, но сам он его не проверяет и не контролирует,
 * так как значение является условием выбора вариант в родителе. В родителе сверяются значения и запускается вариант.
 * Сам вариант исполняет по очереди свои подчиненные объекты, пока один из них не сработает, таким образом
 * автоматически исполнится не больше одного подчиненного объекта.
 *
 * @version 1.0
 */
namespace Library\basic\widgets\Option;

use Boolive\data\Entity,
    Boolive\values\Rule,
    Boolive\template\Template;

class Option extends Entity
{
    public function getInputRule()
    {
        return Rule::arrays(array(
            'GET' => Rule::arrays(array(
                'view' => Rule::string()->default('')->required() // имя виджета, которым отображать принудительно
                ), Rule::any() // не удалять другие элементы
            )), Rule::any() // не удалять другие элементы
        );
    }

    public function work($v = array())
    {
        // Запускаем по очереди подчиненных варианта, пока один из них не сработает
        if ($this->_input['GET']['view']){
            // Если указано, каким отображать, то только его пробуем запустить
            $views = array($this->{$this->_input['GET']['view']});
        }else{
            // Все виджеты варианта
            $views = $this->findAll(array('order'=>'`order` ASC'));
            unset($views['title'], $views['description']);
        }
        $view = reset($views);
        while ($view){
            $v['view'] = $view->start($this->_commands, $this->_input);
            if ($v['view'] != null){
                $this->_input['previous'] = true;
                return $v['view'];
            }
            $view = next($views);
        }
        return null;
    }
}