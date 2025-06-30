<?php

namespace App\Services\WebShopLanguage;

use App\Contracts\ServiceDto;
use App\Repositories\Eloquent\Company\WebShopLanguage\WebShopLanguageRepositoryInterface;
use Illuminate\Http\Request;

class WebShopLanguageService implements WebShopLanguageServiceInterface
{
    protected WebShopLanguageRepositoryInterface $repository;

    public function __construct(WebShopLanguageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllWebShopLanguages(Request $request): ServiceDto
    {
        $webShopLanguages = $this->repository->all();
        return new ServiceDto("Web Shop Languages retrieved successfully.", 200, $webShopLanguages);
    }

}
