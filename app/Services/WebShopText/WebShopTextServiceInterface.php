<?php

namespace App\Services\WebShopText;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface WebShopTextServiceInterface
{

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function getByItem(Request $request): ServiceDto;

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function updateByItem(Request $request): ServiceDto;

}
