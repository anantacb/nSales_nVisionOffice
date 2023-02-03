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
        'DataType' => 'datetime',
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
        'DataType' => 'datetime',
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
        'DataType' => 'datetime',
        'Length' => null,
        'AutoIncrement' => false,
        'Nullable' => true,
        'DefaultValue' => null,
        'PrimaryKey' => false,
        'Unique' => false,
        'SortOrder' => 40
    ]
];
