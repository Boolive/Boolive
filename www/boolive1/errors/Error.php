<?php
/**
 * Базовый класс исключений (ошибок)
 * Позволяет:
 * - группировать и одновременно вызовать множество исключений
 * - формируеть иерархию (к любому ичключению можно добавить множество подчиненных исключений)
 * - получать пользовательское сообщение об исключении
 *
 * - Доступ к вложенным исключениям осуществляется по их коду.
 * - Код исключения может быть строковым
 * $msg = $exception->{sub_code}->{sub_sub_code}->getUserMessage();
 *
 * @version 2.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */

namespace Boolive\errors;

use Exception,
    Boolive\develop\ITrace,
    IteratorAggregate,
    ArrayIterator;

class Error extends Exception implements ITrace, IteratorAggregate
{
    /** @const string Файл со статическими сообщениями */
	const FILE_GLOBAL_DICTIONARY = 'config.error.messages.php';
	/** @var array Глобальный словарь пользовательских сообщений об ошибках*/
	private static $global_dictionary;

    /** @var array Локальный словарь сообщений об ошибках. Дополняет глобальный */
    private $local_dictionary;

    /** @var array Массив временных подчиненных исключений, к которым обращались для проверки существования */
    private $temps;
    /** @var bool Признак, является ли исключение временным? */
    private $is_temp;

    /** @var Error Родительское  исключение */
    private $parent;
    /** @var string|int Код исключения */
    protected $code;
    /** @var array Массив вложенных исключений */
    private $children;
    /** @var array Аргументы для вставки в текст сообщения */
    protected $args;

    /**
     * @param string|array $message Текст сообщения (имя исключения). С помощью массива передаётся текст сообщения и
     * вставляемые в текст переменные
     * @example new Error(array('Text %s incorrect', $text));
     * @param int|string $code Код исключения
     * @param Error $previous Предыдущее исключение. Используется при создания цепочки исключений
     */
    function __construct($message = '', $code = 0, Error $previous = null)
    {
        if (is_array($message)){
            if (sizeof($message)>0){
                $m = array_shift($message);
                if (!empty($message)){
                    if (is_array($message[0])){
                        $this->args = $message[0];
                    }else{
                        $this->args = $message;
                    }
                }
                $message = $m;
            }else{
                $message = '';
            }
        }
        parent::__construct($message, 0, $previous);
        $this->code = $code;
        $this->parent = null;
        $this->children = array();
        $this->temps = array();
        $this->is_temp = false;
    }

    /**
     * Перегрузка метода получения исключения. @example $e = $error->user->min;
     * Всегда возвращется Error, даже если нет запрашиваемого исключения (возвратитя временный Error)
     * @param string $name Имя параметра
     * @return Error
     */
    function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Перегрузка установки исключения: @example $error->user = "Неверный юзер";
     * Вложенному исключению с кодом user устанавливается новое сообщение или полностью переопределяется объект исключение, если его присваивать
     * @param string $code Код (имя) вложенного исключения
     * @param string|Error $error Новое исключение или сообщение исключения
     */
    function __set($code, $error)
    {
        if (!isset($this->children[$code])){
            if (!$error instanceof Error){
                $error = new Error((string)$error, $code);
            }
            $this->children[$code] = $error;
            $this->children[$code]->parent = $this;
        }else{
            if ($error instanceof Error){
                $this->delete($code);
                $this->children[$code] = $error;
                $this->children[$code]->parent = $this;
                if (!$error->code) $error->code = $code;
            }else{
                $this->children[$code]->message = (string)$error;
            }
        }
        $this->untemp();
    }

    function __isset($name)
    {
        return $this->isExist($name);
    }

    /**
     * Добавление исключения
     * @param Error|array|string $error Объект исключения, массив вложенных исключений или код исключения
     * @param string $message Сообщение исключения.
     * @return array|Error |Error
     */
    function add($error, $message = '')
    {
        // Если был временным
        $this->untemp();
        // Добавление подчиненного
        if (is_scalar($error)){
            $this->children[$error] = new Error($message, $error);
            $this->children[$error]->parent = $this;
            return $this->children[$error];
        }else
        if (is_array($error)){
            foreach ($error as $e){
                $this->add($e);
            }
        }else
        if ($error instanceof Error){
            $this->children[$error->code] = $error;
            $this->children[$error->code]->parent = $this;
            if ($message) $error->message = $message;
            return $this->children[$error->code];
        }
        return $this;
    }

    /**
     * Получение исключения с указнным именем (ключом)
     * @param string $name Название (ключ) исключения
     * @return Error
     */
    function get($name)
    {
        if (isset($this->children[$name])){
            $this->children[$name];
        }else
        if (isset($this->temps[$name])){
            $this->temps[$name];
        }else{
            // Делавем временный подчиненный список исключений
            $this->temps[$name] = new Error('Ошибки', $name);
            $this->temps[$name]->is_temp = true;
            $this->temps[$name]->parent = $this;
            return $this->temps[$name];
        }
        return $this->children[$name];
    }

    /**
     * Возвращает вложенные исключения
     * @return array
     */
    function children()
    {
        return $this->children;
    }

    /**
     * Проверка на наличие исключений
     * @param string $name Название (ключ) исключения
     * @return bool
     */
    function isExist($name = null)
    {
        if (isset($name)){
            return isset($this->children[$name]);
        }
        return !empty($this->children);
    }

    /**
     * Удаление всех подчиенных исключений
     */
    function clear()
    {
        unset($this->children, $this->temps);
        $this->children = array();
        $this->temps = array();
    }

    /**
     * Удаление подчиенного исключения
     * @param $name Название (ключ) исключения
     */
    function delete($name)
    {
        if (isset($this->children[$name])){
            $this->children[$name]->parent = null;
            unset($this->children[$name]);
        }else
        if (isset($this->temps[$name])){
            $this->temps[$name]->parent = null;
            unset($this->temps[$name]);
        }
    }

    function setCode($code)
    {
        if ($this->parent && isset($this->parent->children[$this->code])){
            unset($this->parent->children[$this->code]);
            $this->parent->children[$code] = $this;
        }
        $this->code = $code;
    }

    /**
     * Аргументы сообщения
     * @return array
     */
    function getArgs()
    {
        return $this->args;
    }

    /**
     * Пользовательские сообщения об ошибке
     * Возвращаются сообщения либо всех подчиенных исключений, либо только своё сообщение, если нет подчиненных
     * @param bool $all_sub Признак, возратить все сообщения на вложенные исключения?
     * @param string $postfix Строка, которую добавлять в конец каждого сообщения.
     * @return string
     */
    function getUserMessage($all_sub = false, $postfix = "")
    {
        // Объединение сообщений подчиненных исключений
        if ($all_sub && $this->isExist()){
            $message = '';
            foreach ($this->children as $e){
                /** @var $e Error */
                $message.= $e->getUserMessage($all_sub, $postfix);
            }
            return $message;
        }
        // Сообщение по-умолчанию
        return vsprintf($this->getMessageText($postfix), $this->args);
    }

    function getUserMessageList($all_sub = false, $postfix = "")
    {
        // Объединение сообщений подчиненных исключений
        if ($all_sub && $this->isExist()){
            $message = array();
            foreach ($this->children as $e){
                /** @var $e Error */
                $message = array_merge($message, $e->getUserMessageList($all_sub, $postfix));
            }
            return $message;
        }
        // Сообщение по-умолчанию
        return array(vsprintf($this->getMessageText($postfix), $this->args));
    }

    /**
     * Создание исключение из массива, описывающего параметры исключения
     * Обратная функция для __toArray()
     * @param array $errors
     * @return Error
     */
    static function createFromArray($errors)
    {
        if (is_array($errors)){
            if (isset($errors['code'], $errors['message'])){
                $result = new Error($errors['message'], $errors['code']);
                if (isset($errors['children']) && is_array($errors['children'])){
                    foreach ($errors['children'] as $name => $e){
                        $result->add(self::createFromArray($e));
                    }
                }
                return $result;
            }
        }
        return new Error();
    }

    /**
     * Конвертирование исключение в массив
     * @param bool $user_message Признак, возвращать пользовательские сообщения или программные?
     * @return array Многомерный массив с информацией об исключени
     */
    function toArray($user_message = true)
    {
        $result = array(
            'code' => $this->code,
            'message' => $user_message ? $this->getUserMessage() : (empty($this->args)?$this->message:array($this->message, $this->args)),
            'children' => array()
        );
        foreach ($this->children as $name => $e){
            if ($e instanceof Error){
                $result['children'][$name] = $e->toArray($user_message);
            }else{
                /** @var $e Exception */
                $result['children'][$name] = array(
                    'code' => $this->getCode(),
                    'message' => $this->getMessage(),
                );
            }
        }
        return $result;
    }

    function toArrayCompact($user_message = true)
    {
        if ($this->children){
            $result = array();
            foreach ($this->children as $name => $e){
                if ($e instanceof Error){
                    $result[$name] = $e->toArrayCompact($user_message);
                }else{
                    /** @var $e Exception */
                    $result[$name] = $this->getMessage();
                }
            }
        }else{
            $result = $user_message ? $this->getUserMessage() : (empty($this->args)?$this->message:array($this->message, $this->args));
        }
        return $result;
    }

    /**
     * Сообщение об ошибках
     * @return string
     */
    function __toString()
    {
        $result = "{$this->message}\n";
        foreach ($this->children as $e){
            /** @var $e Error */
            $result.=' - '.$e->__toString()."\n";
        }
        return $result;
    }

    /**
     * Итератор по вложенным исключениям (для foreach)
     * @return \ArrayIterator|\Traversable
     */
    function getIterator() {
        return new ArrayIterator($this->children);
    }

    /**
     * Удаление признака временности исключения
     */
    protected function untemp()
    {
        if ($this->is_temp){
            $this->is_temp = false;
            if (isset($this->parent)){
                // В родитле пермещаем себя в основной список
                $this->parent->children[$this->code] = $this;
                unset($this->parent->temps[$this->code]);
                // Возможно, родитель тоже временный
                $this->parent->untemp();
            }
        }
    }

    function trace()
    {
        $trace = array();
        $trace['code'] = $this->getCode();
        $trace['message'] = $this->getMessage();
        if (!empty($this->args)) $trace['message_user'] = $this->getUserMessage(false, '');
        $trace['file'] = $this->getFile();
        $trace['line'] = $this->getLine();
        $trace['children'] = $this->children;
        return $trace;
    }

    /**
     * Текст ошибки из базы или конфига по коду исключения
     * @param string $end
     * @return string
     */
    private function getMessageText($end = "\n")
    {
        // Формирование полного ключа
		$keys = array();
		$curr = $this;
		while ($curr){
			array_unshift($keys, $curr->code);
			if ($curr instanceof self){
				$curr = $curr->parent;
			}else{
				$curr = null;
			}
		}
		// Поиск сообщения в массиве загруженных
        $curr = null;
        $root = $this->getDictionary();
        $cnt = sizeof($keys);
		$i = 0;
        while ($i<$cnt && !$curr){
            if (isset($root[$keys[$i]])){
                $j = $i+1;
                $curr = $root[$keys[$i]];
                while ($j < $cnt && $curr){
                    if (isset($curr[$keys[$j]]) ){
                        $curr = $curr[$keys[$j]];
                        $j++;
                    }else{
                        $curr = null;
                    }
                }
            }
            $i++;
        }

		// Если найдено
		if (is_scalar($curr)){
			return $curr.(preg_match('/'.preg_quote($end,'/').'$/u', $curr)?'':$end);
		}else
		if (is_array($curr) && isset($curr['default'])){
			return $curr['default'].(preg_match('/'.preg_quote($end,'/').'$/u', $curr['default'])?'':$end);
		}
        return $this->message.(preg_match('/'.preg_quote($end,'/').'$/u', $this->message)?'':$end);
    }

    /**
     * Словарь пользовательских сообщений
     * Если не был установлен, то используется словарь родительского исключения или глобальный словарь
     * @return array
     */
    function getDictionary()
    {
        if (isset($this->local_dictionary)){
            return $this->local_dictionary;
        }else
        if ($this->parent){
            return $this->parent->getDictionary();
        }else{
            self::loadGlobalDictionary();
            return self::$global_dictionary;
        }
    }

    /**
     * Установить словарь пользовательских сообщений
     * Формат как в конфиге глобальных сообщений /config.error.messages.php
     * @param $messages_tree
     */
    function setDictionary($messages_tree)
    {
        if ($messages_tree){
            self::loadGlobalDictionary();
            $this->local_dictionary = array_merge_recursive(self::$global_dictionary, $messages_tree);
        }
    }

    /**
     * Удаление словаря пользовательских сообщений об ошибках
     * После удаления используется словарь родительского исключения или глобальный.
     */
    function clearDictionary()
    {
        $this->local_dictionary = null;
    }

    /**
	 * Загрузка пользовательских сообщений из конфига
	 */
	private static function loadGlobalDictionary(){
		if (!isset(self::$global_dictionary)){
			include DIR_SERVER.self::FILE_GLOBAL_DICTIONARY;
			if (!isset($messages)) $messages = array();
			self::$global_dictionary = $messages;
		}
	}
}
