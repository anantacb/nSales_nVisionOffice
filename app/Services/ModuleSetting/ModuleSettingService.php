<?php

namespace App\Services\ModuleSetting;


use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Company\CompanyRepositoryInterface;
use App\Repositories\Eloquent\Office\Module\ModuleRepositoryInterface;
use App\Repositories\Eloquent\Office\ModuleSetting\ModuleSettingRepositoryInterface;
use App\Repositories\Eloquent\Office\Setting\SettingRepositoryInterface;
use Illuminate\Http\Request;

class ModuleSettingService implements ModuleSettingServiceInterface
{
    protected ModuleSettingRepositoryInterface $moduleSettingRepository;
    protected ModuleRepositoryInterface $moduleRepository;
    protected CompanyRepositoryInterface $companyRepository;

    protected SettingRepositoryInterface $settingRepository;

    public function __construct(
        ModuleSettingRepositoryInterface $moduleSettingRepository,
        SettingRepositoryInterface       $settingRepository,
        ModuleRepositoryInterface        $moduleRepository,
        CompanyRepositoryInterface       $companyRepository
    )
    {
        $this->moduleSettingRepository = $moduleSettingRepository;
        $this->moduleRepository = $moduleRepository;
        $this->companyRepository = $companyRepository;
        $this->settingRepository = $settingRepository;
    }

    public function getAllModuleSettingsByCompanyId(Request $request): ServiceDto
    {
        $companyId = $request->get('CompanyId');

        $defaultModuleNames = ['System'];

        $defaultModules = $this->moduleRepository->getByAttributes([
            ['column' => 'Name', 'operand' => '=', 'value' => $defaultModuleNames]
        ]);

        $defaultModuleIds = $defaultModules->pluck('Id')->toArray();

        $companyWithModules = $this->companyRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $companyId]
        ],
            ['modules']
        );
        $companyModuleIds = $companyWithModules->modules->pluck('Id')->toArray();

        $moduleIds = array_merge($defaultModuleIds, $companyModuleIds);

        $relations = [
            'moduleSettings' => function ($q) use ($companyId) {
                $q->select(['Id', 'ModuleId', 'Name', 'DataType', 'Value'])
                    ->with([
                        'setting' => function ($q) use ($companyId) {
                            $q->select(["Id", "ModuleSettingId", "Value", "CompanyId"])
                                ->where('CompanyId', $companyId);
                        },
                    ])
                    ->orderBy('Name');
            }
        ];

        $modules = $this->moduleRepository->getByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $moduleIds]
        ], $relations, ['Id', 'Name'], 'Name');


        $moduleSettings = [];
        foreach ($modules as $module) {
            if ($module->moduleSettings) {
                foreach ($module->moduleSettings as $moduleSetting) {
                    $moduleName = str_replace(' ', '', $module->Name);
                    $moduleSettings[$moduleName][] = $this->formatModuleSetting($moduleSetting);
                }
            }
        }
        //dd($modules->toArray());

        /*$moduleSettings = [];

        $relations = [
            'setting' => function ($q) use ($companyId) {
                $q->where('CompanyId', $companyId)
                    ->select(["Id", "ModuleSettingId", "Value", "ValueExpression", "CompanyId"]);
            },
            'module'
        ];

        $filter_by_relations = [
            [
                "relation" => "module.companies", "column" => "Company.Id", "operator" => "=", "values" => $companyId
            ]
        ];

        $all_module_settings = $this->moduleSettingRepository->getByAttributes([], $relations, [
            "Id", "ModuleId", "Name", "Label", "DataType", "Options", "Value", "ValueExpression", "Note"
        ], '', false, $filter_by_relations);

        foreach ($all_module_settings as $module_setting) {
            $moduleSetting = collect($module_setting)->except(['module'])->toArray();
            $moduleSetting['DefaultValue'] = $moduleSetting['Value'];
            if ($moduleSetting['setting']) {
                $moduleSetting['Value'] = $moduleSetting['setting']['Value'];
            }
            $moduleSettings[$module_setting->module->Name][] = $moduleSetting;
        }*/

        return new ServiceDto("ModuleSettings retrieved!!!", 200, $moduleSettings);
    }

    private function formatModuleSetting($moduleSetting)
    {
        $moduleSetting->DefaultValue = $moduleSetting->Value;
        $moduleSetting->IsJson = false;
        if ($moduleSetting->setting) {
            $moduleSetting->Value = $moduleSetting->setting->Value;
        }

        if ($moduleSetting->DataType == 'Boolean') {
            $moduleSetting->Value = strtolower($moduleSetting->Value);
        }

        if (str_contains($moduleSetting->DataType, 'Enum')) {
            $optionString = substr($moduleSetting->DataType, 5, -1);
            $options = explode(',', $optionString);
            $options = array_map(function ($option) {
                return substr($option, 1, -1);
            }, $options);
            $moduleSetting->EnumOptions = $options;
            $moduleSetting->DataType = 'Enum';
        }

        if ($moduleSetting->DataType == 'Double') {
            $moduleSetting->Value = (double)$moduleSetting->Value;
        }

        if ($moduleSetting->DataType == 'Int32') {
            $moduleSetting->Value = (int)$moduleSetting->Value;
        }

        if ($moduleSetting->DataType == 'String') {
            if (isJSON($moduleSetting->Value)) {
                $moduleSetting->IsJson = true;
                $moduleSetting->Value = json_decode($moduleSetting->Value);
            }
        }

        return $moduleSetting;
    }

    public function updateModuleSettingsByCompanyId(Request $request): ServiceDto
    {
        $companyId = $request->get('CompanyId');

        foreach ($request->get('ModuleSettings') as $moduleSetting) {
            if ($moduleSetting['setting']) {
                $this->settingRepository->findByIdAndUpdate($moduleSetting['setting']['Id'], [
                    'Value' => $moduleSetting['Value']
                ]);
            } else {
                $this->settingRepository->create([
                    'ModuleSettingId' => $moduleSetting['Id'],
                    'Value' => $moduleSetting['Value'],
                    'CompanyId' => $companyId,
                ]);
            }
        }

        return new ServiceDto("Settings Updated Successfully!!!", 200, []);
    }
}
