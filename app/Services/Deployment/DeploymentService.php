<?php

namespace App\Services\Deployment;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Plugin\NsalesAdminDjangoApi\NsalesAdminDjangoApiRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DeploymentService implements DeploymentServiceInterface
{
    private CompanyRepositoryInterface $companyRepository;
    private NsalesAdminDjangoApiRepository $repository;


    public function __construct(NsalesAdminDjangoApiRepository $repository, CompanyRepositoryInterface $companyRepository)
    {
        $this->repository = $repository;
        $this->companyRepository = $companyRepository;
    }

    public function getCompanyDeploymentStatus(Request $request): ServiceDto
    {
        if (Cache::has("company_" . $request->get("CompanyId"))) {
            $company = Cache::get("company_" . $request->get("CompanyId"));
        } else {
            $company = $this->companyRepository->findById($request->get("CompanyId"));
        }

        $response = $this->repository->getCompanyDeploymentStatus($company->DomainName);

        $companyDeploymentStatus = [];
        if ($response['success']) {
            $companyDeploymentStatus = $response['data'];
            return new ServiceDto("Company Deployment Status Retrieved Successfully.", $response['status_code'], $companyDeploymentStatus);
        } else {
            return new ServiceDto($response["message"], $response['status_code'], $companyDeploymentStatus);
        }

    }

    public function startCompanyDeployment(Request $request): ServiceDto
    {
        if (Cache::has("company_" . $request->get("CompanyId"))) {
            $company = Cache::get("company_" . $request->get("CompanyId"));
        } else {
            $company = $this->companyRepository->findById($request->get("CompanyId"));
        }

        $response = $this->repository->startCompanyDeployment($company->DomainName, $request->get("prod"), $request->get("dev"));

        $companyDeploymentStatus = [];
        if ($response['success']) {
            $companyDeploymentStatus = $response['data'];
            return new ServiceDto("Company Deployment Successfully.", $response['status_code'], $companyDeploymentStatus);
        } else {
            return new ServiceDto($response["message"], $response['status_code'], $companyDeploymentStatus);
        }

    }
}
