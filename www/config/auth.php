<?php
/**
 * Настройки модуля авторизации
 */
return array(
    // Эталон нового пользователей
    'user' => '/library/access/User',
    // Группа гостей
    'group_guest' => '/members/guests',
    // Группа зарегистрированных
    'group_registered' => '/members/registered',
    // Идентификаторы (uri) пользователей с безграничеными правами доступа
    'super-admins' => array(
        '/members/registered/admins/admin',
    ),
);