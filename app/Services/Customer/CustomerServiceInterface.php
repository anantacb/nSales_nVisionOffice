<?php

namespace App\Services\Customer;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface CustomerServiceInterface
{
    public function getCustomers(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
