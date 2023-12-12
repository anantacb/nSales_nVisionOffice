<?php

namespace App\Services\CustomerVisit;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\CustomerVisit\CustomerVisitRepositoryInterface;
use Illuminate\Http\Request;

class CustomerVisitService implements CustomerVisitServiceInterface
{
    protected CustomerVisitRepositoryInterface $customerVisitRepository;

    public function __construct(CustomerVisitRepositoryInterface $customerVisitRepository)
    {
        $this->customerVisitRepository = $customerVisitRepository;
    }

    public function getCustomerVisits(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [];
        $customerVisits = $this->customerVisitRepository->paginatedData($request);
        return new ServiceDto("Customer Visits retrieved!!!", 200, $customerVisits);
    }

    public function getDistinctValue($columnName): ServiceDto
    {
        $distinct_values = $this->customerVisitRepository->distinct(
            $columnName,
            [
                [
                    'column' => $columnName,
                    'operand' => '!=',
                    'value' => ''
                ]
            ],
            $columnName
        );

        $data = $distinct_values->map(function ($item) {
            return [
                'label' => $item,
                'value' => $item,
            ];
        });

        return new ServiceDto("Distinct Sales Rep Data", 200, $data);
    }

}
