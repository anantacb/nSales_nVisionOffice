<?php

namespace App\Services\EmailLayout;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface EmailLayoutServiceInterface
{
    public function getEmailLayouts(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;

    public function getDataForPreview(Request $request): ServiceDto;

    public function getEmailLayoutOptionsByLanguage(Request $request): ServiceDto;

    public function getPreviewTemplateObject(): ServiceDto;

}
