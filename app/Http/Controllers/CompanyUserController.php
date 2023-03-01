<?php

namespace App\Http\Controllers;

use App\Services\CompanyUser\CompanyUserServiceInterface;

class CompanyUserController extends Controller
{
    protected CompanyUserServiceInterface $companyUserService;

    public function __construct(CompanyUserServiceInterface $companyUserService)
    {
        $this->companyUserService = $companyUserService;
    }
}
