<?php

namespace App\Services\Customer;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\Customer\CustomerRepositoryInterface;
use Illuminate\Http\Request;

class CustomerService implements CustomerServiceInterface
{
    protected CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function getCustomers(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [];
        $customers = $this->customerRepository->paginatedData($request);
        return new ServiceDto("Customers retrieved!!!", 200, $customers);
    }

    public function create(Request $request): ServiceDto
    {
        $customer = $this->customerRepository->create($request->except(['CompanyId']));
        return new ServiceDto("Customer Created Successfully.", 200, $customer);
    }

    public function update(Request $request): ServiceDto
    {
        $customer = $this->customerRepository->findByIdAndUpdate(
            $request->get('Id'),
            $request->except(['CompanyId'])
        );
        return new ServiceDto("Customer Updated Successfully.", 200, $customer);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->customerRepository->findByIdAndDelete($request->get('CustomerId'));
        return new ServiceDto("Customer Deleted Successfully.", 200);
    }

    public function details(Request $request): ServiceDto
    {
        $attributes = [
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('CustomerId')]
        ];
        /*if ($request->get('initials')) {
            $attributes[] = ['column' => 'Employee', 'operand' => '=', 'value' => $request->get('initials')];
        }*/

        $customer = $this->customerRepository->firstByAttributes($attributes);

        return new ServiceDto("Customer Retrieved Successfully.", 200, $customer);
    }
}
