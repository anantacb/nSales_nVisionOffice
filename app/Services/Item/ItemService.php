<?php

namespace App\Services\Item;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\Item\ItemRepositoryInterface;
use Illuminate\Http\Request;

class ItemService implements ItemServiceInterface
{
    protected ItemRepositoryInterface $repository;

    public function __construct(ItemRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getItems(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [];
        $items = $this->repository->paginatedData($request);

        return new ServiceDto("Items retrieved!!!", 200, $items);
    }

    public function create(Request $request): ServiceDto
    {
        $item = $this->repository->create($request->except(['CompanyId']));
        return new ServiceDto("Item Created Successfully.", 200, $item);
    }

    public function update(Request $request): ServiceDto
    {
        $item = $this->repository->findByIdAndUpdate(
            $request->get('Id'),
            $request->except(['CompanyId'])
        );
        return new ServiceDto("Item Updated Successfully.", 200, $item);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->repository->findByIdAndDelete($request->get('CustomerId'));
        return new ServiceDto("Item Deleted Successfully.", 200);
    }

    public function details(Request $request): ServiceDto
    {
        $attributes = [
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('CustomerId')]
        ];
        /*if ($request->get('initials')) {
            $attributes[] = ['column' => 'Employee', 'operand' => '=', 'value' => $request->get('initials')];
        }*/

        $item = $this->repository->firstByAttributes($attributes);

        return new ServiceDto("Item Retrieved Successfully.", 200, $item);
    }
}
