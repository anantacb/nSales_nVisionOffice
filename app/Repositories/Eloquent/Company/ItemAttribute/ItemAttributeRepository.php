<?php

namespace App\Repositories\Eloquent\Company\ItemAttribute;

use App\Models\Company\Itemattribute;
use App\Repositories\Eloquent\Base\BaseRepository;

class ItemAttributeRepository extends BaseRepository implements ItemAttributeRepositoryInterface
{
    public function __construct(Itemattribute $model)
    {
        parent::__construct($model);
    }

    public function getItemAttributesByItem($itemId)
    {
        return $this->model->whereHas('item', function ($query) use ($itemId) {
            $query->where('Id', $itemId);
        })->select('Id', 'TypeCode', 'Language', 'Value')->get();
    }

}
