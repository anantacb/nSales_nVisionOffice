<?php

namespace App\Services\User;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface UserServiceInterface
{
    public function authUserDetails(): ServiceDto;

    public function createCompanyUser(Request $request): ServiceDto;

    public function getAllCompanyUsers(Request $request): ServiceDto;

    public function getUsers(Request $request): ServiceDto;

    public function getDevelopers(Request $request): ServiceDto;

    public function getCompanyUsers(Request $request): ServiceDto;

    public function companyUserDetails(Request $request): ServiceDto;

    public function updateCompanyUser(Request $request): ServiceDto;
}
