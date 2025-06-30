<?php

namespace App\Services\ItemAttribute;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ItemAttributeServiceInterface
{
    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function getItemAttributesByItem(Request $request): ServiceDto;

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function updateItemAttributesByItem(Request $request): ServiceDto;

    /**
     * @param Request $request
     * @return ServiceDto
     */
    public function delete(Request $request): ServiceDto;

}
