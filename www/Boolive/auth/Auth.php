<?php
/**
 * Модуль аутентификации пользователя
 * Определяет текущего пользователя
 *
 * @version 1.0
 * @author Vladimir Shestakov <boolive@yandex.ru>
 */
namespace Boolive\auth;

use Boolive\data\Data,
    Boolive\input\Input;

class Auth
{
    /** Эталон пользователей */
    const USER = '/Library/access/User';
    /** Группа гостей */
    const GROUP_GUEST = '/members/guests';
    /** Группа зарегистрированных */
    const GROUP_REGISTERED = '/members/registered';
    /** @var \Site\Library\access\User\User Текущий пользователь */
    static private $user;

    /**
     * Текущий пользователь
     * @return \Site\Library\access\Member\Member
     */
    static function getUser()
    {
        // Автоматическая аутентификация пользователя
        if (!isset(self::$user)){
            self::remind();
        }
        return self::$user;
    }

    /**
     * Установка текущего пользователя
     * Используется при "ручной" аутентификации, например, формой входа. При этом поиск пользователя
     * по логину, паролю или другим параметрам выполняется моделью формы входа.
     * @param null | \Site\Library\access\User\User $user Авторизованный пользователь или NULL для отмены авторизации
     * @param int $duration Длительность в секундах запоминания пользователя. Если 0, то пользователь запоминается на период работы браузера
     */
    static function setUser($user, $duration = 0)
    {
        if ($user instanceof \Site\Library\access\User\User){
            self::$user = $user;
            self::remember($duration);
        }else{
            // Забыть текущего пользователя и создать нового гостя
            Input::COOKIE()->offsetUnset('ID');
            self::remind();
        }
        \Boolive\events\Events::trigger('Auth::setUser', array(self::getUser()));
    }

    /**
     * Вспомнить пользователя
     * @return \Site\Library\access\Member\Member
     */
    static function remind()
    {
        self::$user = null;
        if ($ID = Input::COOKIE()->ID->string())  $ID = explode('|', $ID);
        // Период запоминания пользователя
        $duration = empty($ID[0])? 0 : $ID[0]; // не больше месяца (примерно)
        // Хэш пользователя для поиска (авторизации)
        $hash = empty($ID[1]) ? '' : $ID[1];
        // Если есть кука, то ищем пользователя в БД
        if ($hash){
            $result = Data::read(array(
                'from' => '/members',
                'select' => 'children',
                'depth' => 'max',
                'where' => array(
                    array('attr', 'value', '=', $hash),
                    array('attr', 'is_link', '=', '0')
                ),
                'key' => false,
                'limit' => array(0, 1),
                'comment' => 'auth user by cookie'
            ), false);
            // Пользователь найден и не истекло время его запоминания
            if (!empty($result)){
                self::$user = $result[0];
            }
        }else{
            $hash = self::getUniqHash();
        }
        // Новый гость
        if (!self::$user){
            self::$user = Data::read(self::USER, false)->birth(Data::read(self::GROUP_GUEST, false), false);
            self::$user->value($hash);
            $duration = 0;
        }
        self::remember($duration);
    }

    /**
     * Запомнить пользователя для последующего автоматического входа
     * @param int $duration Длительность запоминания в секундах. Если 0, то пользователь запоминается на период работы браузера
     */
    static function remember($duration = 0)
    {
        $duration = max(0, min($duration, 3000000)); // не больше месяца (примерно)
        $hash = self::$user->value(null, true);
        // Запомнить hash
        if (!$hash){
            self::$user->value($hash = self::getUniqHash());
            self::$user->save(false, false);
        }
        // Запомнить время визита (не чаще раза за 5 минут)
//        if (self::$user->isExist() && (Data::read(array(self::$user, 'visit_time'), false)->value() < (time()-300))){
//            // Обновление времени визита
//            self::$user->visit_time = time();
//            //self::$user->visit_time->save(true, false);
//        }
        setcookie('ID', $duration.'|'.$hash, ($duration ? time()+$duration : 0), '/');
    }

    /**
     * Хэширование
     * @param string $string Исходное значение
     * @return string
     */
    static function getHash($string)
    {
        return hash('sha256', $string);
    }

    /**
	 * Уникальное хэш-значение
	 * @return string
	 */
	static function getUniqHash()
    {
		return hash('sha256', uniqid(rand(), true).serialize($_SERVER));
	}
}