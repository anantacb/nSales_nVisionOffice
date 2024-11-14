<?php

namespace App\Services\WebShopText;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\WebShopText\WebShopTextRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
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
        $webShopTexts = $this->getWebShopTextsByItem($request->get('ItemId'));
        return new ServiceDto("Web Shop Texts retrieved successfully.", 200, $webShopTexts);
    }

    /**
     * @param int $itemId
     * @return Collection
     */
    private function getWebShopTextsByItem(int $itemId): Collection
    {
        $webShopTextMinIds = $this->repository->getMinIdsByItem($itemId);

        return $this->repository->getByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $webShopTextMinIds],
            ['column' => 'ElementType', 'operand' => '=', 'value' => 'Item']
        ]);

    }

    public function updateByItem(Request $request): ServiceDto
    {

        foreach ($request->get('WebShopTexts') as $webShopText) {
            $data = [
                'ElementNumber' => $request->get('ItemNumber'),
                'ElementType' => $webShopText['ElementType'],
                'Language' => $webShopText['Language'],
                'Text' => $webShopText['Text'] ?? "",
                'Type' => $webShopText['Type']
            ];

            if (!isset($webShopText['Id'])) {
                $this->repository->create($data);
            } else {
                $this->repository->findByIdAndUpdate($webShopText['Id'], $data);
            }
        }

        $webShopTexts = $this->getWebShopTextsByItem($request->get('ItemId'));
        return new ServiceDto("Web Shop Texts update successfully.", 200, $webShopTexts);
    }

}
