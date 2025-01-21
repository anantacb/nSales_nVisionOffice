<?php

namespace App\Services\B2bGqlApi;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Plugin\B2bGqlApi\B2bGqlApiRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class B2bGqlApiService
{
    private B2bGqlApiRepository $repository;
    private CompanyRepositoryInterface $companyRepository;

    public function __construct(B2bGqlApiRepository $repository, CompanyRepositoryInterface $companyRepository)
    {
        $this->repository = $repository;
        $this->companyRepository = $companyRepository;
    }

    public function getItemGroupsAndItem(Request $request): ServiceDto
    {
        if(Cache::has("company_" . $request->get("CompanyId"))) {
            $company = Cache::get("company_" . $request->get("CompanyId"));
        } else {
            $company = $this->companyRepository->findById($request->get("CompanyId"));
        }

        $response = $this->repository->createLoginToken($company->DomainName, "appsubmission", "appsubmission");
        if($response["success"]) {
            $authToken = $response["data"]["token"];

            $response = $this->repository->getItemgroups($authToken);
            if($response["success"]) {
                $itemGroups = $response["data"];
                foreach ($itemGroups as $itemGroup) {
                    $response = $this->repository->getItemgroupProducts($authToken, $itemGroup["SystemKey"], 20);
                    if($response["success"] && isset($response["data"]["data"]) && count($response["data"]["data"]) > 0) {
                        return new ServiceDto(
                            "Itemgroup products retrieved!",
                            200,
                            [
                                "itemgroup" => $itemGroup,
                                "items" => $response["data"]["data"]
                            ]
                        );
                    }
                }
            }
        }

        if($response["success"]) {
            return new ServiceDto(
                "Itemgroup products retrieved!",
                200,
                [
                    "itemgroup" => $itemGroups,
                    "items" => []
                ]
            );
        }

        throw new \Exception( $response["message"], 404);
    }
}
