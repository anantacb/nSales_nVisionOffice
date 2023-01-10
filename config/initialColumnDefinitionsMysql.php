<?php
return [
    [
        'name' => 'Id',
        'data_type' => 'int',
        'length' => 11,
        'auto_increment' => true,
        'nullable' => false,
        'default' => null,
        'primary_key' => true,
        'unique_key' => false,
        'sort_order' => 10
    ],
    [
        'name' => 'InsertTime',
        'data_type' => 'datetime',
        'length' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false,
        'unique_key' => false,
        'sort_order' => 20
    ],
    [
        'name' => 'UpdateTime',
        'data_type' => 'datetime',
        'length' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false,
        'unique_key' => false,
        'sort_order' => 30
    ],
    [
        'name' => 'DeleteTime',
        'data_type' => 'datetime',
        'length' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false,
        'unique_key' => false,
        'sort_order' => 40
    ],
    /*[
        'name' => 'ImportTime',
        'data_type' => 'datetime',
        'length' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false,
        'unique_key' => false,
    'sort_order => 10
    ],
    [
        'name' => 'ExportTime',
        'data_type' => 'datetime',
        'length' => null,
        'auto_increment' => false,
        'nullable' => true,
        'default' => null,
        'primary_key' => false,
        'unique_key' => false,
    'sort_order => 10
    ]*/
];
