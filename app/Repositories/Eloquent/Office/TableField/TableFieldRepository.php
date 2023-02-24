<?php

namespace App\Repositories\Eloquent\Office\TableField;

use App\Models\Office\TableField;
use App\Repositories\Eloquent\Base\BaseRepository;

class TableFieldRepository extends BaseRepository implements TableFieldRepositoryInterface
{
    public function __construct(TableField $model)
    {
        parent::__construct($model);
    }

    public function companyTableFields($companyId, $tableId)
    {
        $hiddenTableFields = ["Id", "InsertTime", "UpdateTime", "DeleteTime", "ImportTime", "ExportTime",
            "InsertBy", "UpdateBy", "DeleteBy", "ImportBy", "ExportBy"];

        $defaultTableFields = $this->model
            ->where("TableId", $tableId)
            ->whereNotIn("Name", $hiddenTableFields)
            ->whereNotIn("Id", function ($query){
                $query->select('TableFieldId')->from('CompanyTableField');
            });

        $companySpecificTableFields = $this->model
            ->where("TableId", $tableId)
            ->whereNotIn("Name", $hiddenTableFields)
            ->whereIn("Id", function ($query) use($companyId){
                $query->select('TableFieldId')->from('CompanyTableField')->where("CompanyId", $companyId);
            });

        return $defaultTableFields->union($companySpecificTableFields)->get();
    }
}
