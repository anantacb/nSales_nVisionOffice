<?php

namespace App\Repositories\Eloquent\Office\Table;

use App\Repositories\Eloquent\Base\BaseRepositoryInterface;

interface TableFieldRepositoryInterface extends BaseRepositoryInterface
{
    public function companyTableFields($companyId, $tableId);
}
