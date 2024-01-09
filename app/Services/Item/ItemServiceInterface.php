<?php

namespace App\Services\Item;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface ItemServiceInterface
{
    public function getItems(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;
}
