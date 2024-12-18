<?php

namespace App\Services\DocumentApi;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\DocumentApi\DocumentApiRepositoryInterface;
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
        $data = $this->repository->all();
        return new ServiceDto("Company Document api Retrieved Successfully.", 200, $data);
    }
}
