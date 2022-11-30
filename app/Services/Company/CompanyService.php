<?php

namespace App\Services\Company;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use Illuminate\Http\Request;

class CompanyService implements CompanyServiceInterface
{
    protected CompanyRepositoryInterface $companyRepository;

    public function __construct(CompanyRepositoryInterface $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public function getAllCompanies(Request $request): ServiceDto
    {
        $companies = $this->companyRepository->getByAttributes([], '', ['Id', 'Name', 'CompanyName'], 'Name');
        return new ServiceDto("Companies retrieved!!!", 200, $companies);
    }
}
