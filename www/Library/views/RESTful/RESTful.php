<?php
/**
 * RESTful сервис
 * Обработчик запросов на получение, создание, изменение и удаление объектов.
 * @version 1.0
 * @date 29.04.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\views\RESTful;

use Boolive\data\Data;
use Boolive\data\Entity;
use Boolive\errors\Error;
use Boolive\functions\F;
use Boolive\input\Input;
use Library\views\View\View,
    Boolive\values\Rule;

class RESTful extends View
{

    /**
     * Правило на входящие данные - услоdие работы restful
     */
    public function defineInputRule()
    {
        $this->_input_rule = Rule::arrays(array(
                'SERVER' => Rule::arrays(array(
                    'HTTP_ACCEPT' => Rule::ospatterns('*application/json*'),
                    'REQUEST_URI' => Rule::string() // Поисковый запрос для GET/DELETE
                )),
                'REQUEST' => Rule::arrays(array(
                    'path' => Rule::string(), // uri изменяемого объекта (PUT) или в который добавлять новый (POST)
                    'method' => Rule::string(), // метод запроса
                    'call' => Rule::string()->default('')->required(), // вызываемый метод объекта
                    'entity' => Rule::arrays(Rule::string()) // атрибуты изменяемого объекта
                )),
                'FILES' => Rule::arrays(array(
                    'entity' => Rule::arrays(array(
                        'file' => Rule::arrays(Rule::string()) // файл, загружаемый в объект
                    ))
                )),
                'previous' => Rule::not(true)
            )
        );
    }

    public function work()
    {
        switch ($this->_input['REQUEST']['method']){
            // Выбор
            case 'GET':
                $this->get($this->_input['SERVER']['REQUEST_URI']);
                break;
            // Добавление нового объекта в коллекцию
            case 'POST':
            // Редактирование или создание объекта, если его нет
            case 'PUT':
                $attribs = $this->_input['REQUEST']['entity'];
                if (isset($this->_input['FILES']['entity']['file'])){
                    $attribs['file'] = $this->_input['FILES']['entity']['file'];
                }else{
                    if (isset($attribs['file'])) unset($attribs['file']);
                }
                if ($this->_input['REQUEST']['method'] == 'PUT'){
                    $this->put(Data::read($this->_input['REQUEST']['path']), $attribs);
                }else{
                    $this->post(Data::read($this->_input['REQUEST']['path']), $attribs);
                }
                break;
            // Удаление объекта
            case 'DELETE':
                $obj = Data::read($this->_input['REQUEST']['path']);
                if ($obj->isExist()){
                    try{
                        $obj->destroy();
                        header("HTTP/1.1 204 No Content");
                    }catch (Error $e){
                        header("HTTP/1.1 403 Forbidden");
                        echo F::toJSON(array('error' => $e->__toArray()));
                    }
                }else{
                    header("HTTP/1.1 404 Not Found");
                }
                break;
            case 'CALL':

                break;
            default:
                header("HTTP/1.1 501 Not Implemented");
        }
    }

    /**
     * Обработка GET запроса
     * Выбор объекта, списка объекта или дерева объектов по URI
     * @param $uri Условие поиска в URL формате
     */
    private function get($uri)
    {
        // Если есть условие, то выполняется поиск подчиненных объекта
        $result = Data::read($uri);
        if ($result instanceof Entity){
            $result = $result->export(false, true, false);
        }else
        if (is_array($result)){
            /** Перебор объектов или групп выборок */
            foreach ($result as $gkey => $gitem){
                if ($gitem instanceof Entity){
                    $result[$gkey] = $gitem->export(false, true, false);
                }else
                // Если массив, то перебор объектоы в группе выборки
                if (is_array($result)){
                    foreach ($result as $key => $item){
                        if ($item instanceof Entity){
                            $result[$gkey][$key] = $item->export(false, true, false);
                        }
                    }
                }
            }
        }
        header("HTTP/1.1 200 OK");
        header('Content-Type: application/json; charset=UTF-8');
        echo F::toJSON(array('result' => $result));
    }

    /**
     * Обработка PUT запроса.
     * Редактирование объекта. Если объект не существует, то будет создан новый
     * @param Entity $obj Изменяемый объект
     * @param array $attribs Новые значения атрибутов для объекта
     */
    private function put($obj, $attribs)
    {
        // Изменяемый объект
        $create = !$obj->isExist();
        // Название
        if (isset($attribs['name']) && ($attribs['name']!=$obj->name() || !empty($attribs['autoname']))) $obj->name($attribs['name'], true);
        // Значение
        if (empty($attribs['is_default_value'])){
            if (isset($attribs['value'])) $obj->value($attribs['value']);
        }else{
            // Значения по умолчанию от прототипа
            $obj->isDefaultValue(true);
        }
        // Файл
        if (isset($attribs['file'])){
            $obj->file($attribs['file']);
        }else{
            $obj->isFile(!empty($attribs['is_file']));
        }
        if (isset($attribs['proto'])) $obj->proto(Data::read($attribs['proto']));
        if (isset($attribs['lang'])) $obj['lang'] = $attribs['lang'];
        if (isset($attribs['owner'])) $obj['owner'] = $attribs['owner'];
        if (isset($attribs['parent'])) $obj->parent(Data::read($attribs['parent']));
        if (isset($attribs['order'])) $obj->order($attribs['order']);
        if (isset($attribs['is_hidden'])) $obj->isHidden(!empty($attribs['is_hidden']));
        if (isset($attribs['is_delete'])) $obj->isDelete(!empty($attribs['is_delete']));
        if (isset($attribs['is_history'])) $obj->isHistory(!empty($attribs['is_history']));
        if (isset($attribs['is_link'])) $obj->isLink(!empty($attribs['is_link']));
        $class_changed = (bool)$obj->isDefaultClass() != empty($attribs['is_default_class']);
        $obj->isDefaultClass(!empty($attribs['is_default_class']));
        // Проверка и сохранение
        try{
            $obj->save(false, false);
            // Если изменился класс, то повторно выбрать объект из хранилища, чтобы обновилась его логика
            if ($class_changed){
                $this->_input['REQUEST']['object'] = Data::read(array(
                    'from' => $obj->id(),
                    'owner' => $obj->owner(),
                    'lang' => $obj->lang(),
                    'cache' => 0
                ), true);
            }
            $create? header("HTTP/1.1 201 Created") : header("HTTP/1.1 200 OK");
            header('Content-Type: application/json; charset=UTF-8');
            echo F::toJSON(array('result'=>$obj->export(false, true, false)));
        }catch (Error $error){
            header("HTTP/1.1 400 Bad Request");
            header('Content-Type: application/json; charset=UTF-8');
            echo F::toJSON(array('error'=>$error->__toArray(true), 'result'=>$obj->export(false, true, false)));
        }
    }

    /**
     * Добавление нового объекта
     * @param Entity $parent Объект-родитель, в который добавляется новый объект
     * @param array $attribs Атрибуты нового объекта
     */
    private function post($parent, $attribs)
    {
        if (isset($attribs['proto']) && ($proto = Data::read($attribs['proto'])) && $proto->isExist()){
            $obj = $proto->birth($parent);
        }else{
            $obj = new Entity();
            $obj->parent($parent);
        }
        // Название
        if (isset($attribs['name'])) $obj->name($attribs['name'], true);
        // Значение
        if (empty($attribs['is_default_value'])){
            if (isset($attribs['value'])) $obj->value($attribs['value']);
        }else{
            // Значения по умолчанию от прототипа
            $obj->isDefaultValue(true);
        }
        // Файл
        if (isset($attribs['file'])){
            $obj->file($attribs['file']);
        }else{
            $obj->isFile(!empty($attribs['is_file']));
        }
        if (isset($attribs['lang'])) $obj['lang'] = $attribs['lang'];
        if (isset($attribs['owner'])) $obj['owner'] = $attribs['owner'];
        if (isset($attribs['order'])) $obj->order($attribs['order']);
        if (isset($attribs['is_hidden'])) $obj->isHidden(!empty($attribs['is_hidden']));
        if (isset($attribs['is_delete'])) $obj->isDelete(!empty($attribs['is_delete']));
        if (isset($attribs['is_history'])) $obj->isHistory(!empty($attribs['is_history']));
        if (isset($attribs['is_link'])) $obj->isLink(!empty($attribs['is_link']));
        $obj->isDefaultClass(!empty($attribs['is_default_class']));

        // Проверка и сохранение
        try{
            $obj->save(false, false);
            header("HTTP/1.1 201 Created");
            header('Content-Type: application/json; charset=UTF-8');
            echo F::toJSON(array('result'=>$obj->export(false, true, false)));
        }catch (Error $error){
            header("HTTP/1.1 400 Bad Request");
            header('Content-Type: application/json; charset=UTF-8');
            echo F::toJSON(array('error'=>$error->__toArray(true), 'result'=>$obj->export(false, true, false)));
        }
    }
}