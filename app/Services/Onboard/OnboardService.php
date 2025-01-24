<?php

namespace App\Services\Onboard;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\OnboardStatus\OnboardStatusRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OnboardService implements OnboardServiceInterface
{
    private OnboardStatusRepository $onboardStatusRepository;
    private CompanyRepositoryInterface $companyRepository;

    public function __construct(
        OnboardStatusRepository    $onboardStatusRepository,
        CompanyRepositoryInterface $companyRepository
    )
    {
        $this->onboardStatusRepository = $onboardStatusRepository;
        $this->companyRepository = $companyRepository;
    }

    public function getCompanyOnboardStatus(Request $request): ServiceDto
    {
        $attributes = [
            ['column' => 'CompanyId', 'operand' => '=', 'value' => $request->get("CompanyId")],
            ['column' => 'Type', 'operand' => '=', 'value' => strtolower($request->get("Application"))]
        ];

        $onboardStatus = $this->onboardStatusRepository->firstByAttributes($attributes);

        if (Cache::has("company_" . $request->get("CompanyId"))) {
            $company = Cache::get("company_" . $request->get("CompanyId"));
        } else {
            $company = $this->companyRepository->findById($request->get("CompanyId"));
        }

        if (strtolower($request->get("Application")) === "retailer") {
            $onboardStatusSetting = $company->module_settings['System']['RetailerOnboardSteps'] ?? null;
        } else {
            $onboardStatusSetting = $company->module_settings['System']['WebshopOnboardSteps'] ?? null;
        }

        if (!$onboardStatus) {
            $onboardStatus = json_decode($onboardStatusSetting, true);
        } else {
            $onboardStatus = array_merge(json_decode($onboardStatusSetting, true), json_decode($onboardStatus->Progress, true));
        }

        return new ServiceDto("Onboard progress Retrieved Successfully.", 200, $onboardStatus);
    }

    public function updateCompanyOnboardStatus(Request $request): ServiceDto
    {
        $matchingAttributes = [
            "CompanyId" => $request->get("CompanyId"),
            "Type" => strtolower($request->get("Application"))
        ];

        $allStepCompleted = 0;
        foreach ($request->get("Status") as $step) {
            $allStepCompleted = $step["IsCompleted"];
            if(!$allStepCompleted) {
                break;
            }
        }

        $updateAttributes = [
            "Progress" => json_encode($request->get("Status")),
            "Completed" => $allStepCompleted
        ];

        $this->onboardStatusRepository->updateOrCreate($matchingAttributes, $updateAttributes);

        return new ServiceDto("Onboard progress Updated Successfully.", 201);
    }
}
