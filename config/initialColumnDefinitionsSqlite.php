<?php

return [
    [
        'Name' => 'Id',
        'DataType' => 'int',
        'Length' => 11,
        'AutoIncrement' => true,
        'Nullable' => false,
        'DefaultValue' => null,
        'PrimaryKey' => true,
        'Unique' => false,
        'SortOrder' => 10
    ],
    [
        'Name' => 'InsertTime',
        'DataType' => 'timestamp',
        'Length' => null,
        'AutoIncrement' => false,
        'Nullable' => true,
        'DefaultValue' => null,
        'PrimaryKey' => false,
        'Unique' => false,
        'SortOrder' => 20
    ],
    [
        'Name' => 'UpdateTime',
        'DataType' => 'timestamp',
        'Length' => null,
        'AutoIncrement' => false,
        'Nullable' => true,
        'DefaultValue' => null,
        'PrimaryKey' => false,
        'Unique' => false,
        'SortOrder' => 30
    ],
    [
        'Name' => 'DeleteTime',
        'DataType' => 'timestamp',
        'Length' => null,
        'AutoIncrement' => false,
        'Nullable' => true,
        'DefaultValue' => null,
        'PrimaryKey' => false,
        'Unique' => false,
        'SortOrder' => 40
    ]
];
