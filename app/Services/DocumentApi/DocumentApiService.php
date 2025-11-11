<?php

namespace App\Services\DocumentApi;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\DocumentApi\DocumentApiRepositoryInterface;
use App\Services\Company\CompanyService;
use Illuminate\Http\Request;

class DocumentApiService implements DocumentApiServiceInterface
{
    private DocumentApiRepositoryInterface $repository;

    public function __construct(DocumentApiRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getCompanyDocumentApi(Request $request): ServiceDto
    {
        if (!CompanyService::isModuleEnabled('DocumentApi')) {
            return new ServiceDto("Document api Module is not enabled for this company.", 200, []);
        }
        $data = $this->repository->all();
        return new ServiceDto("Company Document api Retrieved Successfully.", 200, $data);
    }
}
