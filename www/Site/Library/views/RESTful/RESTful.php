<?php
/**
 * RESTful сервис
 * Обработчик запросов на получение, создание, изменение и удаление объектов.
 * @version 1.0
 * @date 29.04.2013
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Library\views\RESTful;

use Boolive\data\Data,
    Boolive\data\Entity,
    Boolive\errors\Error,
    Boolive\functions\F,
    Library\views\View\View,
    Boolive\values\Rule;

class RESTful extends View
{
    /**
     * Правило на входящие данные - услоdие работы restful
     */
    function startRule()
    {
        return Rule::arrays(array(
            'SERVER' => Rule::arrays(array(
                'HTTP_ACCEPT' => Rule::ospatterns('*application/json*')->required(),
                'REQUEST_URI' => Rule::string() // Поисковый запрос для GET/DELETE
            )),
            'REQUEST' => Rule::arrays(array(
                'path' => Rule::string(), // uri изменяемого объекта (PUT) или в который добавлять новый (POST)
                'method' => Rule::string(), // метод запроса
                'call' => Rule::string()->default('')->required(), // вызываемый метод объекта
                'entity' => Rule::arrays(Rule::string()), // атрибуты изменяемого объекта
                'file_content' => Rule::int()->default(0)->required(), // Экпортировать файл объекта или нет?
                'class_content' => Rule::int()->default(0)->required() // Экпортировать класс объекта или нет?
            )),
            'FILES' => Rule::arrays(array(
                'entity' => Rule::arrays(array(
                    'file' => Rule::arrays(Rule::string()) // файл, загружаемый в объект
                ))
            )),
            'previous' => Rule::not(true)
        ));
    }

    function work()
    {
        switch ($this->_input['REQUEST']['method']){
            // Выбор
            case 'GET':
                $this->get($this->_input['SERVER']['REQUEST_URI'], $this->_input['REQUEST']['file_content'], $this->_input['REQUEST']['class_content']);
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
                        echo F::toJSON(array('error' => $e->toArray()));
                    }
                }else{
                    header("HTTP/1.1 404 Not Found");
                }
                break;
            case 'CALL':
                $this->call(Data::read($this->_input['REQUEST']['path']), $this->_input['REQUEST']['call'], $this->_input_child);
                break;
            default:
                header("HTTP/1.1 501 Not Implemented");
        }
    }

    /**
     * Обработка GET запроса
     * Выбор объекта, списка объекта или дерева объектов по URI
     * @param $uri Условие поиска в URL формате
     * @param int $export_file Экспортировать файл или нет
     * @param int $export_class Экспортировать класс или нет
     */
    private function get($uri, $export_file, $export_class)
    {
        // Если есть условие, то выполняется поиск подчиненных объекта
        $result = Data::read($uri);
        if ($result instanceof Entity){
            $result = $result->export(false, true, false, $export_file, $export_class);
        }else
        if (is_array($result)){
            /** Перебор объектов или групп выборок */
            foreach ($result as $gkey => $gitem){
                if ($gitem instanceof Entity){
                    $result[$gkey] = $gitem->export(false, true, false, $export_file, $export_class);
                }else
                // Если массив, то перебор объектоы в группе выборки
                if (is_array($result)){
                    foreach ($result as $key => $item){
                        if ($item instanceof Entity){
                            $result[$gkey][$key] = $item->export(false, true, false, $export_file, $export_class);
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
            if (isset($attribs['is_file'])) $obj->isFile(!empty($attribs['is_file']));
        }
        if (isset($attribs['proto'])) $obj->proto(Data::read($attribs['proto']));
        if (isset($attribs['parent'])) $obj->parent(Data::read($attribs['parent']));
        if (isset($attribs['order'])) $obj->order($attribs['order']);
        if (isset($attribs['is_hidden'])) $obj->isHidden(!empty($attribs['is_hidden']));
        if (isset($attribs['is_draft'])) $obj->isDraft(!empty($attribs['is_draft']));
        if (isset($attribs['is_link'])) $obj->isLink(!empty($attribs['is_link']));
        if (isset($attribs['is_relative'])) $obj->isRelative(!empty($attribs['is_relative']));
        if (isset($attribs['is_mandatory'])) $obj->isMandatory($attribs['is_mandatory']);
        $class_changed = isset($attribs['is_default_class']) && (bool)$obj->isDefaultClass() != empty($attribs['is_default_class']);
        if (isset($attribs['is_default_class'])){
            $obj->isDefaultClass(!empty($attribs['is_default_class']));
        }
        // Проверка и сохранение
        try{
            $obj->save(false);
            // Если изменился класс, то повторно выбрать объект из хранилища, чтобы обновилась его логика
            if ($class_changed){
                $this->_input['REQUEST']['object'] = Data::read(array(
                    'from' => $obj->id(),
                    'cache' => 0
                ), true);
            }
            $create? header("HTTP/1.1 201 Created") : header("HTTP/1.1 200 OK");
            header('Content-Type: application/json; charset=utf-8');
            echo F::toJSON(array('result'=>$obj->export(false, true, false)));
        }catch (Error $error){
            header("HTTP/1.1 400 Bad Request");
            header('Content-Type: application/json; charset=utf-8');
            echo F::toJSON(array('error'=>$error->toArray(true), 'result'=>$obj->export(false, true, false)));
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
            $obj = $proto->birth($parent, false);
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
        if (isset($attribs['order'])) $obj->order($attribs['order']);
        if (isset($attribs['is_hidden'])) $obj->isHidden(!empty($attribs['is_hidden']));
        if (isset($attribs['is_draft'])) $obj->isDraft(!empty($attribs['is_draft']));
        if (isset($attribs['is_link'])) $obj->isLink(!empty($attribs['is_link']));
        if (isset($attribs['is_default_class'])){
            $obj->isDefaultClass(!empty($attribs['is_default_class']));
        }
        // Проверка и сохранение
        try{
            $obj->save(false);
            header("HTTP/1.1 201 Created");
            header('Content-Type: application/json; charset=UTF-8');
            echo F::toJSON(array('result'=>$obj->export(false, true, false)));
        }catch (Error $error){
            header("HTTP/1.1 400 Bad Request");
            header('Content-Type: application/json; charset=UTF-8');
            echo F::toJSON(array('error'=>$error->toArray(true), 'result'=>$obj->export(false, true, false)));
        }
    }

    /**
     * Вызов метода объекта
     * @param Entity $object Объект, чей метод вызывается
     * @param string $call Название метода
     * @param array $input Входящие данные
     */
    private function call($object, $call, $input)
    {
        if ($object->isExist()){
            $call = 'call_'.$call;
            $result = $object->$call($input);
        }else{
            $result = null;
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo F::toJSON(array('result'=>$result));
    }
}