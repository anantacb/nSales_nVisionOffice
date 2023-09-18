<?php

namespace App\Repositories\Eloquent\Office\TableIndex;

use App\Models\Office\TableIndex;
use App\Repositories\Eloquent\Base\BaseRepository;

class TableIndexRepository extends BaseRepository implements TableIndexRepositoryInterface
{
    public function __construct(TableIndex $model)
    {
        parent::__construct($model);
    }

    public function companyTableIndices($companyId, $tableId)
    {
        $defaultTableFields = $this->model
            ->where("TableId", $tableId)
            ->whereNotIn("Id", function ($query) {
                $query->select('TableIndexId')->from('CompanyTableIndex');
            });

        $companySpecificTableFields = $this->model
            ->where("TableId", $tableId)
            ->whereIn("Id", function ($query) use ($companyId) {
                $query->select('TableFieldId')->from('CompanyTableIndex')->where("CompanyId", $companyId);
            });

        return $defaultTableFields->union($companySpecificTableFields)->get();
    }
}
