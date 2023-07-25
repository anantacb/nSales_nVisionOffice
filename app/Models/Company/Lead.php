<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Session;

class Lead extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'Lead';

    //protected $appends = ['business_card'];

    public function getBusinessCardAttribute(): ?string
    {
        $selected_company = Session::get('selected_company');
        $url = 'https://www.nvisionoffice.com/Storage/Companies' .
            '/' . $selected_company->DomainName .
            '/Lead/BusinessCardImages/Lead.' . $this->UUID . '.jpg';

        $file_headers = @get_headers($url);
        if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            return null;
        } else {
            return $url;
        }
    }
}
