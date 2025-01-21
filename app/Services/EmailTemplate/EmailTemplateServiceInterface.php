<?php

namespace App\Services\EmailTemplate;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

interface EmailTemplateServiceInterface
{
    public function getEmailTemplates(Request $request): ServiceDto;

    public function create(Request $request): ServiceDto;

    public function details(Request $request): ServiceDto;

    public function update(Request $request): ServiceDto;

    public function delete(Request $request): ServiceDto;

    public function getEmailEvents(Request $request): ServiceDto;

    public function getDataForPreview(Request $request): ServiceDto;
    
    public function getEmailTemplatesForCompany(Request $request): ServiceDto;
}
