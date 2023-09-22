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
            'ApplicationId' => $request->get('ApplicationId'),
            'ModuleId' => $request->get('ModuleId'),
            'AlwaysEnabled' => $request->get('AlwaysEnabled'),
            'ApplicationVersionStart' => $request->get('ApplicationVersionStart') ? $request->get('ApplicationVersionStart') : "",
            'ApplicationVersionEnd' => $request->get('ApplicationVersionEnd') ? $request->get('ApplicationVersionEnd') : "",
            'Title' => $request->get('Title'),
            'SubTitle' => $request->get('SubTitle'),
            'Description' => $request->get('Description'),
        ]);
        return new ServiceDto("Modules Assigned to Application Successfully.", 200);
    }

    public function update(Request $request): ServiceDto
    {
        $applicationModule = $this->applicationModuleRepository->findByIdAndUpdate($request->get('Id'), [
            //'ApplicationId' => $request->get('ApplicationId'),
            //'ModuleId' => $request->get('ModuleId'),
            'AlwaysEnabled' => $request->get('AlwaysEnabled'),
            'ApplicationVersionStart' => $request->get('ApplicationVersionStart') ? $request->get('ApplicationVersionStart') : "",
            'ApplicationVersionEnd' => $request->get('ApplicationVersionEnd') ? $request->get('ApplicationVersionEnd') : "",
            'Title' => $request->get('Title'),
            'SubTitle' => $request->get('SubTitle'),
            'Description' => $request->get('Description'),
        ]);

        return new ServiceDto("Modules Removed from Application Successfully.", 200, $applicationModule);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->applicationModuleRepository->findByIdAndDelete($request->get('ApplicationModuleId'));
        return new ServiceDto("Modules Removed from Application Successfully.", 200);
    }
}
