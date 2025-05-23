<?php

namespace App\Repositories\Eloquent\Company\WebShopText;

use App\Models\Company\WebShopText;
use App\Repositories\Eloquent\Base\BaseRepository;
use Illuminate\Support\Facades\DB;

class WebShopTextRepository extends BaseRepository implements WebShopTextRepositoryInterface
{
    public function __construct(WebShopText $model)
    {
        parent::__construct($model);
    }

    /**
     * @param $itemId
     * @return array
     */
    public function getMinIdsByItem($itemId): array
    {
        return $this->model->whereHas('item', function ($query) use ($itemId) {
            $query->where('Id', $itemId);
        })
            ->select(DB::raw('MIN(Id) as Id'))->groupBy(["Type", "Language"])
            ->where('ElementType', 'Item')->get()->values()->pluck("Id")->toArray();
    }

}
