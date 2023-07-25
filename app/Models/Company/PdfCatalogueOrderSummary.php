<?php

namespace App\Models\Company;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfCatalogueOrderSummary extends BaseModel
{
    protected $connection = 'mysql_company';
    protected $table = 'PdfCatalogueOrderSummary';

    public function pdfCatalogue(): BelongsTo
    {
        return $this->belongsTo(PdfCatalogue::class, 'PdfCatalogueNumber', 'Number');
    }
}
