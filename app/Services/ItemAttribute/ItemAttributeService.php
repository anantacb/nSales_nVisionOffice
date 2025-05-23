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

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function updateItemAttributesByItem(Request $request): ServiceDto
    {
        foreach ($request->get('ItemAttributes') as $itemAttribute) {
            $data = [
                'TypeCode' => $itemAttribute['TypeCode'],
                'Language' => $itemAttribute['Language'],
                'Value' => $itemAttribute['Value'],
                'ItemNumber' => $request->get('ItemNumber')
            ];

            if (!isset($itemAttribute['Id'])) {
                $this->repository->create($data);
            } else {
                $this->repository->findByIdAndUpdate($itemAttribute['Id'], $data);
            }
        }

        $itemAttributes = $this->repository->getItemAttributesByItem($request->get('ItemId'));

        return new ServiceDto("Itemattribute updated successfully.", 200, $itemAttributes);
    }

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function getItemAttributesByItem(Request $request): ServiceDto
    {
        $itemAttributes = $this->repository->getItemAttributesByItem($request->get('ItemId'));

        return new ServiceDto("Itemattribute retrieved successfully.", 200, $itemAttributes);
    }

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function delete(Request $request): ServiceDto
    {
        $this->repository->findByIdAndDelete($request->get('ItemAttributeId'));

        return new ServiceDto("Itemattribute deleted successfully.", 200, []);
    }

}
