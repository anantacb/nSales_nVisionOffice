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
            return new ServiceDto("Company Deployment Status Retrieved Successfully.", 200, $companyDeploymentStatus);
        } else {
            return new ServiceDto($response["message"], 400, $companyDeploymentStatus);
        }

    }

    public function startCompanyDeployment(Request $request): ServiceDto
    {
        if (Cache::has("company_" . $request->get("CompanyId"))) {
            $company = Cache::get("company_" . $request->get("CompanyId"));
        } else {
            $company = $this->companyRepository->findById($request->get("CompanyId"));
        }

        $response = $this->repository->startCompanyDeployment($company->DomainName);

        $companyDeploymentStatus = [];
        if ($response['success']) {
            $companyDeploymentStatus = $response['data'];
            return new ServiceDto("Company Deployment Successfully.", 200, $companyDeploymentStatus);
        } else {
            return new ServiceDto($response["message"], 400, $companyDeploymentStatus);
        }

    }
}
