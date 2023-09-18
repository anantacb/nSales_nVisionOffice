<?php

namespace App\Repositories\Eloquent\Office\TableIndex;

use App\Repositories\Eloquent\Base\BaseRepositoryInterface;

interface TableIndexRepositoryInterface extends BaseRepositoryInterface
{
    public function companyTableIndices($companyId, $tableId);
}
