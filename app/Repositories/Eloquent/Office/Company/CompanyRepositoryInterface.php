<?php

namespace App\Repositories\Eloquent\Office\Company;


use App\Repositories\Eloquent\Base\BaseRepositoryInterface;

interface CompanyRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveCompanies();

    public function credentialFromSettingModule($fields, $companyId);

    public function updateSettings($settings, $companyId);

    public function getModuleIdsBySettingsName($settingNames);

    public function getCompanyModuleIds($companyId);
}
