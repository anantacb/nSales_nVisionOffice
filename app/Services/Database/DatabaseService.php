<?php

namespace App\Services\Database;

use App\Contracts\ServiceDto;
use App\Jobs\CopyDatabaseProdToDev;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use Illuminate\Http\Request;

class DatabaseService implements DatabaseServiceInterface
{
    protected CompanyRepositoryInterface $companyRepository;

    public function __construct(
        CompanyRepositoryInterface $companyRepository,
    )
    {
        $this->companyRepository = $companyRepository;
    }

    public function getAllCompanies(Request $request): ServiceDto
    {
        $companies = $this->companyRepository->getByAttributes(
            [],
            [],
            ['Id', 'Name', 'CompanyName', 'DatabaseName'],
            'Name'
        );
        return new ServiceDto("Companies retrieved!!!", 200, $companies);
    }

    public function copyDBtoDevServer(Request $request): ServiceDto
    {
        $selectedDatabases = $request->get('selectedDatabases');

        foreach ($selectedDatabases as $database) {
            //$dbType = stripos($database, 'NVISION_OFFICE') !== false ? 'office' : 'company';
            $dbType = strtoupper($database) === 'NVISION_OFFICE' ? 'office' : 'company';

            CopyDatabaseProdToDev::dispatch($dbType, $database)->onQueue('db-copy');
        }

        return new ServiceDto("Database copy jobs have been queued successfully!", 200);
    }

}
