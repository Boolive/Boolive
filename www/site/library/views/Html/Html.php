<?php
/**
 * HTML разметка
 * Определяет заголовки HTML документа, подключая необходимые теги в <HEAD>, требуемые виджетами
 * @version 1.2
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace site\library\views\Html;

use boolive\data\Data2;
use boolive\values\Rule,
    boolive\input\Input,
    site\library\views\Widget\Widget;

class Html extends Widget
{
    function startRule()
    {
        return Rule::arrays(array('previous' => Rule::not(true)));
    }

    function show($v = array(), $commands, $input)
    {
        // Вызов всех подчиенных, чтобы исполнить после их команды добавления тегов
        $v = $this->startChildren();

        // Обработка своих команд для вставки тегов в заголовок HTML
        if ($redirect = $this->_commands->get('redirect')){
            header('Location: '.$redirect[0][0]);
            return true;
        }
        $v['head'] = '';
        $js = '';
        $this->_commands->htmlHead('base', array('href'=>'http://'.Input::SERVER()->HTTP_HOST->string().'/'), true);
        // Meta
        $site = Data2::read();
        if ($site->favicon->isExist()){
            $this->_commands->htmlHead('link', array('rel'=>'shortcut icon', 'type'=>$site->favicon->mime(), 'href'=>$site->favicon->file().'?'.$site->favicon->date(true)));
        }
        $v['meta'] = array(
            'title' => $site->title->isExist()? array($site->title->value()) : array(),
            'description' => $site->description->isExist()? array($site->description->value()) : array(),
            'keywords' => array(),
        );
        $uniq = array();
        foreach ($this->_commands->get('htmlHead') as $com){
            if ($com[0]=='title'){
                $v['meta'][$com[0]][] = $com[1]['text'];
            }else
            if ($com[0]=='meta' && in_array($com[1]['name'], array('description', 'keywords'))){
                $v['meta'][$com[1]['name']][] = $com[1]['content'];
            }else
            if (empty($com[2]) || empty($uniq[$com[0]])){
                if (isset($com[1]['text'])){
                    $text = $com[1]['text'];
                    unset($com[1]['text']);
                }else{
                    $text = false;
                }
                $attr = '';
                foreach ($com[1] as $name => $value) $attr.=' '.$name.'="'.$value.'"';
                if ($text === false){
                    $tag = '<'.$com[0].$attr."/>\n";
                }else{
                    $tag = '<'.$com[0].$attr.'>'.$text.'</'.$com[0].">\n";
                }
                if ($com[0] == 'script'){
                    $js.=$tag;
                }else{
                    $v['head'].=$tag;
                }
                $uniq[$com[0]] = true;
            }
        }
        $v['head'].=$js;
        return parent::show($v, $commands, $input);
    }
}
