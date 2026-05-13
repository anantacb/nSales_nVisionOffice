<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCname implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $baseDomain = ltrim((string) $value, 'www.');
        $domainsToCheck = [$baseDomain, 'www.' . $baseDomain];

        foreach ($domainsToCheck as $domain) {
            $records = dns_get_record($domain, DNS_CNAME);
            foreach ($records as $record) {
                if (isset($record['target']) && str_ends_with($record['target'], 'nsales.io')) {
                    return;
                }
            }
        }

        $fail('The :attribute must have a CNAME pointing to nsales.io');
    }
}
