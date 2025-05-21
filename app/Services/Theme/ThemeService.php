<?php

namespace App\Services\Theme;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Office\Theme\ThemeRepositoryInterface;
use App\Services\Google\GoogleBuildServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThemeService implements ThemeServiceInterface
{
    private ThemeRepositoryInterface $repository;
    private GoogleBuildServiceInterface $googleBuildService;

    public function __construct(ThemeRepositoryInterface $repository, GoogleBuildServiceInterface $googleBuildService)
    {
        $this->repository = $repository;
        $this->googleBuildService = $googleBuildService;
    }

    public function getThemes(): ServiceDto
    {
        $data = $this->repository->getThemes();
        return new ServiceDto("Themes Retrieved Successfully.", 200, $data);
    }

    public function getCompanyTheme(Request $request): ServiceDto
    {
        $data = $this->repository->getCompanyTheme($request->get("CompanyId"));
        return new ServiceDto("Company Theme Retrieved Successfully.", 200, $data);
    }

    public function triggerBuild($themeId): ServiceDto
    {
        $theme = $this->repository->getTheme($themeId);
        foreach ($theme->companyTheme as $companyTheme) {
            try {
                $domainName = $companyTheme->company ? $companyTheme->company->DomainName : null;
                if ($domainName) {
                    $this->googleBuildService->triggerBuild($companyTheme->company->DomainName);
                }
            } catch (\Exception $exception) {
                Log::error("Google build trigger failed for CompanyId: " . $companyTheme->CompanyId . " - Error: " . $exception->getMessage());
            }
        }

        return new ServiceDto("Theme build triggered Successfully.", 200);
    }
}
