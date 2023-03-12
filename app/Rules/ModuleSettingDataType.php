<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ModuleSettingDataType implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure(string): PotentiallyTranslatedString $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail): void
    {
        $validDataTypes = ['Boolean', 'Double', 'Int32', 'String'];
        preg_match("/^Enum\((('\w+'),*)+\)$/", $value, $matches);
        if (!in_array($value, $validDataTypes) && !$matches) {
            $fail("The :attribute value is invalid");
        }
    }
}
