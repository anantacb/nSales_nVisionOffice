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
}
