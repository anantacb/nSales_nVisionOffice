<?php

namespace App\Http\Controllers;

use App\Models\Office\Table;

class TableController extends Controller
{
    //

    public function __construct()
    {
    }

    public function getTables()
    {
        $tables = Table::with('companyTables.company')->get();

        $tables->transform(function ($table) {
            $table->companies = $table->companyTables->pluck('company.Name');
            return $table;
        });
        return $tables;
    }
}
