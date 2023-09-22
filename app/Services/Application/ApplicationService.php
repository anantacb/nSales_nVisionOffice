<?php

namespace App\Services\Application;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Application\ApplicationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationService implements ApplicationServiceInterface
{
    protected ApplicationRepositoryInterface $applicationRepository;

    public function __construct(ApplicationRepositoryInterface $applicationRepository)
    {
        $this->applicationRepository = $applicationRepository;
    }

    public function getAllApplications(Request $request): ServiceDto
    {
        $applications = $this->applicationRepository->getByAttributes([], '', ['Id', 'Name'], 'Name');
        return new ServiceDto("Applications retrieved!!!", 200, $applications);
    }

    public function create(Request $request): ServiceDto
    {
        $application = $this->applicationRepository->create([
            'Name' => $request->get('Name'),
            'Platform' => $request->get('Platform'),
            'OperatingSystem' => $request->get('OperatingSystem')
        ]);
        return new ServiceDto("Application Created Successfully.", 200, $application);
    }

    public function update(Request $request): ServiceDto
    {
        $application = $this->applicationRepository->findByIdAndUpdate(
            $request->get('Id'),
            [
                'Name' => $request->get('Name'),
                'Platform' => $request->get('Platform'),
                'OperatingSystem' => $request->get('OperatingSystem')
            ]
        );
        return new ServiceDto("Application Updated Successfully.", 200, $application);
    }

    public function getApplications(Request $request): ServiceDto
    {
        $request = $request->all();
        $applications = $this->applicationRepository->paginatedData($request);
        return new ServiceDto("Applications retrieved!!!", 200, $applications);
    }

    public function delete(Request $request): ServiceDto
    {
        $this->applicationRepository->findByIdAndDelete($request->get('ApplicationId'));
        return new ServiceDto("Application Deleted Successfully.", 200);
    }

    public function details(Request $request): ServiceDto
    {
        $relations = [
            'modules' => function ($q) {
                $q->withPivot([
                    'Id',
                    'AlwaysEnabled',
                    'ApplicationVersionStart',
                    'ApplicationVersionEnd',
                    'Title',
                    'SubTitle',
                    'Description',
                ])->select([DB::raw('Module.Id as Id'), 'Name']);
            }
        ];

        $application = $this->applicationRepository->firstByAttributes([
            ['column' => 'Id', 'operand' => '=', 'value' => $request->get('ApplicationId')]
        ], $relations);

        return new ServiceDto("Application Retrieved Successfully.", 200, $application);
    }
}
