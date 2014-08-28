<?php
return [
    'dbname' => 'boolive-data2-23',
    'host' => '127.0.0.1',
    'port' => '3306',
    'user' => 'root',
    'password' => '',
    'options' => [
        1002 => 'SET NAMES "utf8" COLLATE "utf8_bin"',
    ],
    'prefix' => '',
    'trace_sql' => false,
    'trace_count' => true,
    'sections' => [
        [
            'code' => 0,
            'uri' => '',
        ],
        [
            'code' => 1,
            'uri' => '/library',
        ],
    ],
    'class' => '\\boolive\\data\\stores\\MySQLStore2',
    'connect' => [
        'dbname' => 'boolive-data2-25',
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
        ],
    ],
];
