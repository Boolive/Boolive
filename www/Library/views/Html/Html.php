<?php
/**
 * HTML разметка
 * Определяет заголовки HTML документа, подключая необходимые теги в <HEAD>, требуемые виджетами
 * @version 1.2
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\views\Html;

use Boolive\values\Rule,
    Boolive\input\Input,
    Library\views\Widget\Widget;

class Html extends Widget
{
    public function getInputRule()
    {
        return Rule::arrays(array('previous' => Rule::not(true)));
    }

    public function work($v = array())
    {
        // Вызов всех подчиенных, чтобы исполнить после их команды добавления тегов
        $v = $this->startChildren();

        // Обработка своих команд для вставки тегов в заголовок HTML
        if ($redirect = $this->_commands->get('redirect')){
            header('Location: '.$redirect[0][0]);
            return true;
        }
        $v['head'] = '';
        $this->_commands->addHtml('base', array('href'=>'http://'.Input::SERVER()->HTTP_HOST->string().DIR_WEB), 'unic');
        $uniq = array();
        foreach ($this->_commands->get('addHtml') as $com){
            if (empty($com[2]) || empty($uniq[$com[0]])){
                if (in_array($com[0], array("link", "meta", "script", "title", "base"))){
                    if (isset($com[1]['text'])){
                        $text = $com[1]['text'];
                        unset($com[1]['text']);
                    }else{
                        $text='';
                    }
                    if (isset($com[1]['src'])){
                        if (mb_strpos($com[1]['src'], '?')===false){
                            $com[1]['src'] = $com[1]['src'].'?'.TIMESTAMP;
                        }else{
                            $com[1]['src'] = $com[1]['src'].'&'.TIMESTAMP;
                        }

                    }
                    if (isset($com[1]['href']) && $com[0]!='base') $com[1]['href'] = $com[1]['href'].'?'.TIMESTAMP;
                    $attr = '';
                    foreach ($com[1] as $name => $value) $attr.=' '.$name.'="'.$value.'"';
                    $v['head'].= '<'.$com[0].$attr.'>';
                    if ($com[0] == "script" || $com[0] == "title"){
                        $v['head'].= $text.'</'.$com[0].'>';
                    }
                    $v['head'].="\n";
                    $uniq[$com[0]] = true;
                }
            }
        }
        return parent::work($v);
    }
}
