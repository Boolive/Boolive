<?php
return [
    'class' => '\\boolive\\data\\stores\\MySQLStore2',
    'connect' => [
        'dbname' => 'boolive-data2-36',
        'user' => 'root',
        'password' => '',
        'host' => '127.0.0.1',
        'port' => 3306,
        'prefix' => '',
        'options' => [
            1002 => 'SET NAMES "utf8" COLLATE "utf8_bin"',
        ],
        'trace_sql' => false,
        'trace_count' => false,
        'sections' => [
            [
                'code' => 0,
                'uri' => '',
            ],
            [
                'code' => 1,
                'uri' => '/library',
            ],
            [
                'code' => 2,
                'uri' => '/contents',
            ],
        ],
    ],
];
