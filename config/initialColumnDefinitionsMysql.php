<?php
return [
    [
        'column' => 'Id',
        'data_type' => 'int',
        'size' => 11,
        'auto_increment' => true,
        'nullable' => false,
        'default' => null,
        'primary_key' => true,
        'unique_key' => false,
        'sort_order' => 10
    ],
    [
        'column' => 'InsertTime',
        'data_type' => 'datetime',
        'size' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false,
        'unique_key' => false,
        'sort_order' => 20
    ],
    [
        'column' => 'UpdateTime',
        'data_type' => 'datetime',
        'size' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false,
        'unique_key' => false,
        'sort_order' => 30
    ],
    [
        'column' => 'DeleteTime',
        'data_type' => 'datetime',
        'size' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false,
        'unique_key' => false,
        'sort_order' => 40
    ],
    /*[
        'column' => 'ImportTime',
        'data_type' => 'datetime',
        'size' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false,
        'unique_key' => false,
    'sort_order => 10
    ],
    [
        'column' => 'ExportTime',
        'data_type' => 'datetime',
        'size' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false,
        'unique_key' => false,
    'sort_order => 10
    ]*/
];
