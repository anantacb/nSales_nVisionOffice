<?php

namespace App\Services\ApplicationModule;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\ApplicationModule\ApplicationModuleRepositoryInterface;
use Illuminate\Http\Request;

class ApplicationModuleService implements ApplicationModuleServiceInterface
{
    protected ApplicationModuleRepositoryInterface $applicationModuleRepository;

    public function __construct(ApplicationModuleRepositoryInterface $applicationModuleRepository)
    {
        $this->applicationModuleRepository = $applicationModuleRepository;
    }

    public function create(Request $request): ServiceDto
    {
        $this->applicationModuleRepository->create([
            'ApplicationId' => $request->input('ApplicationId'),
            'ModuleId' => $request->input('ModuleId'),
            'AlwaysEnabled' => $request->input('AlwaysEnabled'),
            'ApplicationVersionStart' => $request->input('ApplicationVersionStart') ? $request->input('ApplicationVersionStart') : "",
            'ApplicationVersionEnd' => $request->input('ApplicationVersionEnd') ? $request->input('ApplicationVersionEnd') : "",
            'Title' => $request->input('Title'),
            'SubTitle' => $request->input('SubTitle'),
            'Description' => $request->input('Description'),
        ]);
        return new ServiceDto("Modules Assigned to Application Successfully.", 200);
    }

    public function update(Request $request): ServiceDto
    {
        $applicationModule = $this->applicationModuleRepository->findByIdAndUpdate($request->input('Id'), [
            //'ApplicationId' => $request->input('ApplicationId'),
            //'ModuleId' => $request->input('ModuleId'),
            'AlwaysEnabled' => $request->input('AlwaysEnabled'),
            'ApplicationVersionStart' => $request->input('ApplicationVersionStart') ? $request->input('ApplicationVersionStart') : "",
            'ApplicationVersionEnd' => $request->input('ApplicationVersionEnd') ? $request->input('ApplicationVersionEnd') : "",
            'Title' => $request->input('Title'),
            'SubTitle' => $request->input('SubTitle'),
            'Description' => $request->input('Description'),
        ]);

        return new ServiceDto("Modules Removed from Application Successfully.", 200, $applicationModule);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->applicationModuleRepository->findByIdAndDelete($request->input('ApplicationModuleId'));
        return new ServiceDto("Modules Removed from Application Successfully.", 200);
    }
}
