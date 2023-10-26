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
        $ApplyTo = $request->get('ApplyTo');

        $customer = $this->customerRepository->findByIdAndUpdate(
            $request->get('Id'),
            [
                'Name' => $request->get('Name'),
                'TemplateType' => $request->get('TemplateType'),
                'Disabled' => $request->get('Disabled'),
                'From' => $request->get('From'),
                'To' => $request->get('To'),
                'Cc' => $request->get('Cc'),
                'Bcc' => $request->get('Bcc'),
                'SendToCompany' => $request->get('SendToCompany'),
                'SendToUser' => $request->get('SendToUser'),
                'SendToCustomer' => $request->get('SendToCustomer'),
                'SendToSupplier' => $request->get('SendToSupplier'),
                'Subject' => $request->get('Subject'),
                'Body' => $request->get('Body'),
                'Description' => $request->get('Description'),
                'TemplatePath' => $request->get('TemplatePath'),
                'ModuleId' => $request->get('ModuleId'),
                'ApplicationId' => $ApplyTo == 'Application' ? $request->get('ApplicationId') : null,
                'CompanyId' => $ApplyTo == 'Company' ? $request->get('CompanyId') : null,
                'RoleId' => $ApplyTo == 'Role' ? $request->get('RoleId') : null,
                'CompanyUserId' => $ApplyTo == 'User' ? $request->get('CompanyUserId') : null,
            ]
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
        $relations = [
            'role' => function ($q) {
                $q->select(['Id', 'Type', 'CompanyId', 'Name']);
            },
            'companyUser' => function ($q) {
                $q->select(['Id', 'UserId', 'CompanyId']);
            }
        ];

        $customer = $this->customerRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('EmailConfigurationId')]
        ], $relations);

        return new ServiceDto("Customer Retrieved Successfully.", 200, $customer);
    }
}
