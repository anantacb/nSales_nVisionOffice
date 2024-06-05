<?php

namespace App\Services\ItemAttribute;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\ItemAttribute\ItemAttributeRepositoryInterface;
use Illuminate\Http\Request;

class ItemAttributeService implements ItemAttributeServiceInterface
{
    protected ItemAttributeRepositoryInterface $repository;

    public function __construct(ItemAttributeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getItemAttributesByItem(Request $request): ServiceDto
    {
        $itemAttributes = $this->repository->getItemAttributesByItem($request->get('ItemId'));

        return new ServiceDto("Itemattribute retrieved successfully.", 200, $itemAttributes);
    }

}
