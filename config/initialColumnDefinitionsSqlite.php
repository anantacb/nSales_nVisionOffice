<?php
return [
    [
        'column' => 'Id',
        'type' => 'int',
        'size' => 11,
        'auto_increment' => true,
        'nullable' => false,
        'default' => null,
        'primary_key' => true
    ],
    [
        'column' => 'InsertTime',
        'type' => 'datetime',
        'size' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false
    ],
    [
        'column' => 'UpdateTime',
        'type' => 'datetime',
        'size' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false
    ],
    [
        'column' => 'DeleteTime',
        'type' => 'datetime',
        'size' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false
    ],
    /*[
        'column' => 'ImportTime',
        'type' => 'datetime',
        'size' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false
    ],
    [
        'column' => 'ExportTime',
        'type' => 'datetime',
        'size' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false
    ]*/
];
