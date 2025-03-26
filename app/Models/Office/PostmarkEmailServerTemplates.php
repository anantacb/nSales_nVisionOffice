<?php

namespace App\Models\Office;

use App\Models\BaseModel;

class PostmarkEmailServerTemplates extends BaseModel
{
    protected $table = 'PostmarkEmailServerTemplates';

    protected $casts = [
        'TemplateDetails' => 'array',
    ];
}
