<?php

namespace App\Services\CompanyEmailTemplate;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface CompanyEmailTemplateServiceInterface
{
    public function getEmailTemplates(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;

    public function getEmailEvents(Request $request): ServiceDto;

    public function getDataForPreview(Request $request): ServiceDto;
}
