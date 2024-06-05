<?php

namespace App\Services\ItemAttribute;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ItemAttributeServiceInterface
{
    public function getItemAttributesByItem(Request $request): ServiceDto;

}
