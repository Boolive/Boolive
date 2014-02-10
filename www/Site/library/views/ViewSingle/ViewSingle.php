<?php
/**
 * Единственный вид
 * Автоматичеки исполненяет подчиненные объекты (виды) пока не будет получен положительный результат.
 * Полностью выполняет свою работу только один подчиненный объект (вид).
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Site\library\views\ViewSingle;

use Boolive\cache\Cache;
use Boolive\input\Input;
use Site\library\views\View\View,
    Boolive\values\Rule,
    Boolive\commands\Commands;

class ViewSingle extends View
{
   protected $_views;

    function startRule()
    {
        return Rule::arrays(array(
            'REQUEST' => Rule::arrays(array(
                'object' => Rule::any(
                    Rule::arrays(Rule::entity($this->value())),
                    Rule::entity($this->value())
                )->required(),
                'view_name' => Rule::string()->default('')->required(), // имя виджета, которым отображать принудительно
            ))
        ));
    }

    function startCheck(Commands $commands, $input)
    {
        if ($result = parent::startCheck($commands, $input)){
            if (!empty($this->_input['REQUEST']['view_name'])){
                // Если указано, каким отображать, то только его пробуем запустить
                $result = $this->{$this->_input['REQUEST']['view_name']}->isExist();
            }
        }
        return $result;
    }

    function work()
    {
//        $key = 'ViewSingle/'.$this->id().'/'.md5(Cache::getId($this->_input_child));

        // Запускаем по очереди подчиненных, пока один из них не сработает
        if ($this->_input['REQUEST']['view_name']){
            // Если указано, каким отображать, то только его пробуем запустить
            $views = array($this->{$this->_input['REQUEST']['view_name']}->linked());
            unset($this->_input_child['REQUEST']['view_name']);
//        }else
//        if ($view_name = Cache::get($key)){
//            $views = array($this->{json_decode($view_name)}->linked());
        }else{
            // Все виды дл запуска
            $views = $this->getViews();
        }
        $view = reset($views);
        while ($view){
            /** @var View $view */
            $out = $view->start($this->_commands, $this->_input_child);
            if ($out !== false){
//                if (!isset($view_name)) Cache::set($key, $view->name());
                return $out;
            }
            $view = next($views);
        }
        return false;
    }

    protected function getViews()
    {
        if (!isset($this->_views)){
            $this->_views = $this->find(array('key'=>'name', 'comment' => 'read views in ViewSingle'));
            foreach ($this->_views as $key => $view){
                $this->_views[$key] = $view->linked();
                if (!$this->_views[$key] instanceof View){
                    unset($this->_views[$key]);
                }
            }
            //unset($this->_views['title'], $this->_views['description']);
        }
        return $this->_views;
    }
}