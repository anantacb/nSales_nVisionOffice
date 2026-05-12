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
        $ApplyTo = $request->input('ApplyTo');
        $emailConfiguration = $this->emailConfigurationRepository->create([
            'Name' => $request->input('Name'),
            'TemplateType' => $request->input('TemplateType'),
            'Disabled' => $request->input('Disabled'),
            'From' => $request->input('From'),
            'To' => $request->input('To'),
            'Cc' => $request->input('Cc'),
            'Bcc' => $request->input('Bcc'),
            'SendToCompany' => $request->input('SendToCompany'),
            'SendToUser' => $request->input('SendToUser'),
            'SendToCustomer' => $request->input('SendToCustomer'),
            'SendToSupplier' => $request->input('SendToSupplier'),
            'SendToEmployee' => $request->input('SendToEmployee'),
            'Subject' => $request->input('Subject'),
            'Body' => $request->input('Body'),
            'Description' => $request->input('Description'),
            'TemplatePath' => $request->input('TemplatePath'),
            'ModuleId' => $request->input('ModuleId'),
            'ApplicationId' => $ApplyTo == 'Application' ? $request->input('ApplicationId') : null,
            'CompanyId' => $ApplyTo == 'Company' ? $request->input('CompanyId') : null,
            'RoleId' => $ApplyTo == 'Role' ? $request->input('RoleId') : null,
            'CompanyUserId' => $ApplyTo == 'User' ? $request->input('CompanyUserId') : null,
            'SendDraftOrderEmail' => $request->input('SendDraftOrderEmail') ?? 0,
        ]);
        return new ServiceDto("Email Configuration Created Successfully.", 200, $emailConfiguration);
    }

    public function update(Request $request): ServiceDto
    {
        $ApplyTo = $request->input('ApplyTo');

        $emailConfiguration = $this->emailConfigurationRepository->findByIdAndUpdate(
            $request->input('Id'),
            [
                'Name' => $request->input('Name'),
                'TemplateType' => $request->input('TemplateType'),
                'Disabled' => $request->input('Disabled'),
                'From' => $request->input('From'),
                'To' => $request->input('To'),
                'Cc' => $request->input('Cc'),
                'Bcc' => $request->input('Bcc'),
                'SendToCompany' => $request->input('SendToCompany'),
                'SendToUser' => $request->input('SendToUser'),
                'SendToCustomer' => $request->input('SendToCustomer'),
                'SendToSupplier' => $request->input('SendToSupplier'),
                'SendToEmployee' => $request->input('SendToEmployee'),
                'Subject' => $request->input('Subject'),
                'Body' => $request->input('Body'),
                'Description' => $request->input('Description'),
                'TemplatePath' => $request->input('TemplatePath'),
                'ModuleId' => $request->input('ModuleId'),
                'ApplicationId' => $ApplyTo == 'Application' ? $request->input('ApplicationId') : null,
                'CompanyId' => $ApplyTo == 'Company' ? $request->input('CompanyId') : null,
                'RoleId' => $ApplyTo == 'Role' ? $request->input('RoleId') : null,
                'CompanyUserId' => $ApplyTo == 'User' ? $request->input('CompanyUserId') : null,
                'SendDraftOrderEmail' => $request->input('SendDraftOrderEmail') ?? 0,
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
        $this->emailConfigurationRepository->findByIdAndDelete($request->input('EmailConfigurationId'));
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
            ['column' => 'Id', 'operand' => '=', 'value' => $request->input('EmailConfigurationId')]
        ], $relations);

        return new ServiceDto("Email Configuration Retrieved Successfully.", 200, $emailConfiguration);
    }
}
