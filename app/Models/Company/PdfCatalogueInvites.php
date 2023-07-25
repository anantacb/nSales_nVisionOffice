<?php

namespace App\Models\Company;


use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfCatalogueInvites extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'PdfCatalogueInvites';

    public function pdfCatalogue(): BelongsTo
    {
        return $this->belongsTo(PdfCatalogue::class, 'PdfCatalogueNumber', 'Number');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Live::class, 'CustomerAccount', 'Account');
    }
}
