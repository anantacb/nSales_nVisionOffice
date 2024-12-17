<?php

namespace App\Services\WebShopPage;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\WebShopPage\WebShopPageRepositoryInterface;
use App\Repositories\Eloquent\Company\WebShopText\WebShopTextRepositoryInterface;
use Illuminate\Http\Request;

class WebShopPageService implements WebShopPageServiceInterface
{
    private WebShopPageRepositoryInterface $webShopPageRepository;
    private WebShopTextRepositoryInterface $webShopTextRepository;

    public function __construct(
        WebShopPageRepositoryInterface $webShopPageRepository,
        WebShopTextRepositoryInterface $webShopTextRepository
    )
    {
        $this->webShopPageRepository = $webShopPageRepository;
        $this->webShopTextRepository = $webShopTextRepository;
    }

    public function list(Request $request): ServiceDto
    {
        $request = $request->except("CompanyId");

        $attributes = [];
        foreach ($request as $column => $value) {
            $attributes[] = [
                'column' => $column, 'operand' => '=', 'value' => $value
            ];
        }
        $pages = $this->webShopPageRepository->getByAttributes($attributes, ["webShopText"]);

        return new ServiceDto("Pages by platform retrieved!!!", 200, $pages);
    }

    public function createPages(Request $request): ServiceDto
    {
        $pages = $request->get("pages");

        $textTypes = ["Header", "SubHeader", "Body", "Footer"];

        foreach ($pages as $page) {
            $input = [
                "Name" => $page["Name"],
                "Type" => "Page",
                "SystemKey" => $page["SystemKey"],
                "Platform" => $page["Platform"],
                "Priority" => 99
            ];
            $response = $this->webShopPageRepository->create($input);

            foreach ($page["languages"] as $langCode) {
                foreach ($textTypes as $type) {
                    $this->webShopTextRepository->create(
                        ["ElementType" => "Page", "ElementNumber" => $response->Id, "Language" => $langCode, "Type" => $type, "Text" => ""]
                    );
                }
            }
        }

        return new ServiceDto("Pages created!!!", 200);
    }

    public function createPagesContentForMissingLanguages(Request $request): ServiceDto
    {
        $pages = $request->get("pages");
        $types = ["Header", "SubHeader", "Body", "Footer"];
        if (count($pages) > 0) {
            foreach ($pages as $pageId => $langCodes) {
                foreach ($langCodes as $langCode) {
                    foreach ($types as $type) {
                        $this->webShopTextRepository->firstOrCreate(
                            ["ElementType" => "Page", "ElementNumber" => $pageId, "Language" => $langCode, "Type" => $type],
                            ["Text" => ""]
                        );
                    }
                }
            }
        }

        return new ServiceDto("Missing content created!!!", 200);
    }
}
