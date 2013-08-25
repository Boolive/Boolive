<?php
/**
 * Внешнее хранилище
 * Доступ к объектам по HTTP
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\data\stores;

use Boolive\data\Data;
use Boolive\data\Entity;
use Boolive\errors\Error;
use Boolive\file\File;
use Boolive\functions\F;

class HTTPStore extends Entity
{
    /** @var string Ключ хранилища, по которому хранилище выбирается для объектов и создаются короткие URI */
    private $key;
    private $curl;

    /**
     * Конструктор экземпляра хранилища
     * @param array $key Ключ хранилища. Используется для формирования и распознования сокращенных URI
     * @param $config Параметры подключения к базе данных
     */
    public function __construct($key, $config)
    {
        $this->key = $key;
        $this->curl = curl_init();
        curl_setopt_array($this->curl, array(
            CURLOPT_HEADER => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json'
            )
        ));
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * Чтение объектов
     * @param $cond Условие на читаемые объекты.
     * @param bool $index Признак, выполнять индексацию данных перед чтением или нет?
     * @return array|\Boolive\data\Entity|null Массив объектов. Если глубина поиска ровна 0, то возвращается объект или null
     * @throws \Exception Ошибки curl
     */
    public function read($cond, $index = false)
    {
        $url = Data::urlencodeCond($cond);
        $base_url = parse_url($url);
        $base_url = $base_url['scheme'].'://'.$base_url['host'];
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPGET => true,
            //CURLOPT_COOKIE => 'XDEBUG_SESSION="netbeans-xdebug"'
        ));
        $result = curl_exec($this->curl);
        if ($result === false){
            $error = curl_errno($this->curl);
            if ($error == 28){
                // timeout
                // @todo возвратить несуществующие объекты
            }else{
                throw new \Exception(curl_error($this->curl), $error);
            }
        }
        $httpcode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $response = json_decode($result, true);
        if (isset($response['result'])) $response = $response['result'];
        if (!is_array($cond['from'])) $response = array($response);
        $result = array();
        foreach ($response as $key => $group){
            $gkey = $base_url.$key;
            if (is_array($group)){
                // Если первый элемент массив, то результат - список объектов
                if (is_array(reset($group)) || empty($group)){
                    // Список объектов
                    $result[$gkey] = array();
                    foreach ($group as $name => $attr){
                        $result[$gkey][$name] = $this->makeObject($attr, $base_url);
                    }
                }else{
                    $result[$gkey] = $this->makeObject($group, $base_url);
                }
            }else
            if ($group === 'null'){
                $result[$gkey] = null;
            }
            if ($group === 'false'){
                $result[$gkey] = false;
            }
        }
        return is_array($cond['from'])?$result:reset($result);
    }

    /**
     * Сохранение объекта
     * @param \Boolive\data\Entity $entity Сохраняемый объект
     * @param $access Признак, проверять доступ или нет? (не используется хранищищем)
     * @throws \Boolive\errors\Error Ошибки в сохраняемом объекте
     * @throws \Exception Ошибки curl
     * @return bool Признак, сохранен объект или нет?
     */
    public function write($entity, $access)
    {
        if ($entity->check($error)){
            try{
                $attr = $entity->export(false, true, false);
                // Файл
                if (!empty($entity->_attribs['file']['tmp_name'])){
                    $path = DIR_SERVER.'../httpstore/'.uniqid().$entity->_attribs['file']['name'];
                    if ($entity->_attribs['file']['tmp_name']!=$path){
                        File::upload($entity->_attribs['file']['tmp_name'], $path);
                        $delete_file = true;
                    }
                    $attr['file'] = '@'.$path.';type='.$entity->_attribs['file']['type'];
                }
                if (!$entity->isExist() && $entity->_autoname){
                    // Добавление нового объекта с подбором уникального имени
                    $attr['autoname'] = true;
                    $url = $entity->parentUri().'/'; // запрос к коллекции (списку объектов)
                    $method = 'POST';
                }else{
                    if ($entity->_autoname){
                        $attr['autoname'] = true;
                    }
                    // Редактирование или создание объекта с указанным имененем
                    $url = $attr['uri']; // запрос к ресурсу (объекту)
                    $method = 'PUT';
                }
                $base_url = parse_url($url);
                $base_url = $base_url['scheme'].'://'.$base_url['host'];
                if (isset($attr['id'])) $attr['id'] = $this->localURI($attr['id'], $base_url);
                if (isset($attr['uri'])) $attr['uri'] = $this->localURI($attr['uri'], $base_url);
                if (isset($attr['parent'])) $attr['parent'] = $this->localURI($attr['parent'], $base_url);
                if (isset($attr['proto'])) $attr['proto'] = $this->localURI($attr['proto'], $base_url);
                if (!empty($attr['is_link'])) $attr['is_link'] = true;
                $attr['is_default_class'] = !empty($attr['is_default_class']);
                $attr['is_default_value'] = !empty($attr['is_default_value']);
                if (isset($attr['owner'])) $attr['owner'] = $this->localURI($attr['owner'], $base_url);
                if (isset($attr['lang'])) $attr['lang'] = $this->localURI($attr['lang'], $base_url);

                curl_setopt_array($this->curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_POST => true,
                    //CURLOPT_CUSTOMREQUEST => $method,
                    CURLOPT_POSTFIELDS => $this->buildQuery(array(
                        'entity' => $attr,
                        'method' => $method
                    )),
                    //CURLOPT_COOKIE => 'XDEBUG_SESSION="netbeans-xdebug"'
                ));
                $result = curl_exec($this->curl);
                if ($result === false)  throw new \Exception(curl_error($this->curl), curl_errno($this->curl));

                $httpcode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
                $response = json_decode($result, true);
                if (isset($response['result'])){
                    $entity->_attribs['id'] = $this->absoluteURI($response['result']['id'], $base_url);
                    $entity->_attribs['uri'] = $this->absoluteURI($response['result']['uri'], $base_url);
                    $entity->_attribs['date'] = intval($response['result']['date']);
                    $entity->_attribs['name'] = $response['result']['name'];
                    $entity->_attribs['value'] = $response['result']['value'];
                    $entity->_attribs['is_file'] = !empty($response['result']['is_file']);
                    $entity->_attribs['is_exist'] = 1;
                    $entity->_changed = false;
                    $entity->_autoname = false;
                    if ($entity->_attribs['uri'] != $response['result']['uri']){
                        $entity->updateURI();
                    }
                }
                if (!empty($delete_file)){
                    File::delete($path);
                }
                if (isset($response['error'])){
                    throw Error::__fromArray($response['error']);
                }
            }catch (\Exception $e){
                throw $e;
            }
        }else{
            throw $error;
        }
        return false;
    }

    /**
     * Удаление объекта и его подчиненных, если они никем не используются
     * @param Entity $entity Уничтожаемый объект
     * @param bool $access Признак, проверять или нет наличие доступа на уничтожение объекта? (не используется хранилщем)
     * @param bool $integrity Признак, проверять целостность данных? (не используется хранилищем)
     * @throws \Boolive\errors\Error Ошибки в сохраняемом объекте
     * @throws \Exception Ошибки curl
     * @return bool
     */
    public function delete($entity, $access, $integrity)
    {
        curl_setopt_array($this->curl, array(
            CURLOPT_URL => $entity->uri(),
            CURLOPT_POST => true,
            //CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => array(
                'method' => 'DELETE'
            ),
            //CURLOPT_COOKIE => 'XDEBUG_SESSION="netbeans-xdebug"'
        ));
        $result = curl_exec($this->curl);
        if ($result === false)  throw new \Exception(curl_error($this->curl), curl_errno($this->curl));
        $httpcode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $response = json_decode($result, true);
        if (isset($response['error'])){
            throw Error::__fromArray($response['error']);
        }
        return $httpcode == '204';
    }

    /**
     * Параметры запроса из многомерного массива для CURL
     * @param $arrays Исходный массив
     * @param array $new Используется функцией при рекурсивоном вызове. Преобразованный массив для CURL
     * @param null $prefix Используется функцией при рекурсивоном вызове. Префикс к ключам массива
     * @return array
     */
    public function buildQuery($arrays, &$new = array(), $prefix = null)
    {
        foreach ($arrays as $key => $value) {
            $k = isset($prefix) ? $prefix.'['.$key.']' : $key;
            if (is_array($value)){
                $this->buildQuery($value, $new, $k);
            }else{
                $new[$k] = $value;
            }
        }
        return $new;
    }

    /**
     * Создание объекта из атрибутов
     * @param array $attribs Атриубты объекта
     * @param $base_url
     * @return Entity
     */
    public function makeObject($attribs, $base_url)
    {
        if (!empty($attribs['children']) && is_array($attribs['children'])){
            foreach ($attribs['children'] as $name => $child){
                $child['name'] = $name;
                $attribs['children'][$name] = $this->makeObject($child, $base_url);
            }
        }
        if (!empty($attribs['id'])) $attribs['id'] = $this->absoluteURI($attribs['id'], $base_url);
        if (isset($attribs['uri']) && $attribs['uri']!='entity') $attribs['uri'] = $this->absoluteURI($attribs['uri'], $base_url);
        if (!empty($attribs['proto'])) $attribs['proto'] = $this->absoluteURI($attribs['proto'], $base_url);
        if (!empty($attribs['parent'])) $attribs['parent'] = $this->absoluteURI($attribs['parent'], $base_url);
        if (!empty($attribs['is_link']) && $attribs['is_link']!=Entity::ENTITY_ID) $attribs['is_link'] = $this->absoluteURI($attribs['is_link'], $base_url);
        if (!empty($attribs['is_default_value']) && $attribs['is_default_value']!=Entity::ENTITY_ID) $attribs['is_default_value'] = $this->absoluteURI($attribs['is_default_value'], $base_url);
        if (!empty($attribs['is_default_class']) && $attribs['is_default_class']!=Entity::ENTITY_ID) $attribs['is_default_class'] = $this->absoluteURI($attribs['is_default_class'], $base_url);
        //$attribs['is_default_class'] = Entity::ENTITY_ID;
        $attribs['class_name'] = '\Boolive\data\Entity';
        return $attribs;
    }


    /**
     * Абсолютный URL преобразовывает в относительный, если схема и домен совпадает с указанной в $base_url
     * Относительный URL превращается в абсолютный на свой домен
     * @param $uri
     * @param $base_url
     * @return string
     */
    private function localURI($uri, $base_url)
    {
        $l = mb_strlen($base_url);
        if (mb_substr($uri, 0, $l) == $base_url){
            return mb_substr($uri, $l);
        }
        if (!preg_match('|^[a-z]+:\/\/|u', $uri)){
            return 'http://'.HTTP_HOST.$uri;
        }
        return $uri;
    }

    /**
     * Преобразование URI во внешний абсолютный.
     * Добавляется схема и домен ($base_url) если её нету
     * @param $uri
     * @param $base_url
     * @return string
     */
    private function absoluteURI($uri, $base_url)
    {
        if (!preg_match('|^[a-z]+:\/\/|u', $uri)){
            return $base_url.$uri;
        }
        return $uri;
    }

    /**
	 * Проверка системных требований для установки класса
	 * @return array
	 */
	static function systemRequirements(){

    }
}