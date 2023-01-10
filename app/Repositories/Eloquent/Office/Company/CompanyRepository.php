<?php

namespace App\Repositories\Eloquent\Office\Company;

use App\Models\Office\Company;
use App\Models\Office\Setting;
use App\Repositories\Eloquent\Base\BaseRepository;
use Illuminate\Support\Facades\DB;


class CompanyRepository extends BaseRepository implements CompanyRepositoryInterface
{
    public function __construct(Company $model)
    {
        parent::__construct($model);
    }

    public function getActiveCompanies()
    {
        return $this->model->where("Disabled", 0)->orderBy("Name")->get();
    }

    public function credentialFromSettingModule($fields, $companyId)
    {
        return DB::table('ModuleSetting')
            ->leftJoin('Setting', 'ModuleSetting.Id', '=', 'Setting.ModuleSettingId')->where('Setting.CompanyId', $companyId)
            ->whereIn('ModuleSetting.Name', $fields)
            ->get(['ModuleSetting.Name', 'Setting.Value'])
            ->mapWithKeys(function ($setting, $key) {
                return [$setting->Name => $setting->Value];
            });

    }

    public function updateSettings($settings, $companyId)
    {
        $settingsFields = DB::table('ModuleSetting')
            ->whereIn('ModuleSetting.Name', array_keys($settings))
            ->get(['ModuleSetting.Name', 'ModuleSetting.Id'])
            ->mapWithKeys(function ($setting, $key) {
                return [$setting->Name => $setting];
            });

        foreach ($settings as $setting => $value) {
            $field = $settingsFields[$setting];
            Setting::updateOrCreate([
                "ModuleSettingId" => $field->Id,
                "CompanyId" => $companyId,
            ],[
                "Value" => $value
            ]);
        }
    }

    public function getModuleIdsBySettingsName($settingNames)
    {
        return DB::table('ModuleSetting')
            ->whereIn("Name", $settingNames)
            ->distinct("ModuleId")
            ->pluck("ModuleId")
            ->toArray();
    }

    public function getCompanyModuleIds($companyId)
    {
        return DB::table('CompanyModule')
            ->where("CompanyId", $companyId)
            ->pluck("ModuleId")
            ->toArray();
    }
}
