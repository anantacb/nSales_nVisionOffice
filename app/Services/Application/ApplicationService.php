<?php

namespace App\Services\Application;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Application\ApplicationRepositoryInterface;
use Illuminate\Http\Request;

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
}
