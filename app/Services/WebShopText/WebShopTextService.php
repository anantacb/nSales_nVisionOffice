<?php

namespace App\Services\WebShopText;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\WebShopText\WebShopTextRepositoryInterface;
use Illuminate\Http\Request;

class WebShopTextService implements WebShopTextServiceInterface
{
    protected WebShopTextRepositoryInterface $repository;

    public function __construct(WebShopTextRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getByItem(Request $request): ServiceDto
    {
        $webShopTextMinIds = $this->repository->getMinIdsByItem($request->get('ItemId'));

        $webShopTexts = $this->repository->getByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $webShopTextMinIds],
            ['column' => 'ElementType', 'operand' => '=', 'value' => 'Item']
        ]);

        return new ServiceDto("Web Shop Texts retrieved successfully.", 200, $webShopTexts);
    }

}
