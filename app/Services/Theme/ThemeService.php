<?php

namespace App\Services\Theme;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Theme\ThemeRepositoryInterface;
use Illuminate\Http\Request;

class ThemeService implements ThemeServiceInterface
{
    private ThemeRepositoryInterface $repository;

    public function __construct(ThemeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getCompanyTheme(Request $request): ServiceDto
    {
        $data = $this->repository->getCompanyTheme($request->get("CompanyId"));
        return new ServiceDto("Company Theme Retrieved Successfully.", 200, $data);
    }
}
