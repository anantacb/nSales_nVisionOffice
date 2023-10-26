<?php

namespace App\Repositories\Eloquent\Company\Customer;

use App\Models\Company\Customer;
use App\Repositories\Eloquent\Base\BaseRepository;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }
}
