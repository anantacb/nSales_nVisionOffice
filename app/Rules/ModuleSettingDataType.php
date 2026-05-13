<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ModuleSettingDataType implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validDataTypes = ['Boolean', 'Double', 'Int32', 'String'];
        preg_match("/^Enum\((('\w+'),*)+\)$/", (string) $value, $matches);
        if (!in_array($value, $validDataTypes) && !$matches) {
            $fail("The :attribute value is invalid");
        }
    }
}
