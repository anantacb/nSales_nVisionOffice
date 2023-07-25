<?php

namespace App\Services\EmailConfiguration;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\EmailConfiguration\EmailConfigurationRepositoryInterface;
use Illuminate\Http\Request;

class EmailConfigurationService implements EmailConfigurationServiceInterface
{
    protected EmailConfigurationRepositoryInterface $emailConfigurationRepository;

    public function __construct(EmailConfigurationRepositoryInterface $emailConfigurationRepository)
    {
        $this->emailConfigurationRepository = $emailConfigurationRepository;
    }

    public function create(Request $request): ServiceDto
    {
        $ApplyTo = $request->get('ApplyTo');
        $emailConfiguration = $this->emailConfigurationRepository->create([
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
        ]);
        return new ServiceDto("Email Configuration Created Successfully.", 200, $emailConfiguration);
    }

    public function update(Request $request): ServiceDto
    {
        $ApplyTo = $request->get('ApplyTo');

        $emailConfiguration = $this->emailConfigurationRepository->findByIdAndUpdate(
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
        return new ServiceDto("Email Configuration Updated Successfully.", 200, $emailConfiguration);
    }

    public function getEmailConfigurations(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            [
                "name" => "module", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "application", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "role", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "company", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "user", "columns" => ['UserId', 'Name']
            ],
        ];
        $emailConfigurations = $this->emailConfigurationRepository->paginatedData($request);
        return new ServiceDto("Email Configurations retrieved!!!", 200, $emailConfigurations);
    }

    public function getCompanyEmailConfigurations(Request $request): ServiceDto
    {
        $request = $request->all();
        $request['relations'] = [
            [
                "name" => "module", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "application", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "role", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "company", "columns" => ['Id', 'Name']
            ],
            [
                "name" => "user", "columns" => ['UserId', 'Name']
            ],
        ];
        $emailConfigurations = $this->emailConfigurationRepository->paginatedCompanyWiseData($request);
        return new ServiceDto("Email Configurations retrieved!!!", 200, $emailConfigurations);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->emailConfigurationRepository->findByIdAndDelete($request->get('EmailConfigurationId'));
        return new ServiceDto("Email Configuration Deleted Successfully.", 200);
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

        $emailConfiguration = $this->emailConfigurationRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('EmailConfigurationId')]
        ], $relations);

        return new ServiceDto("Email Configuration Retrieved Successfully.", 200, $emailConfiguration);
    }
}
