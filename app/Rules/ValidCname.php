<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidCname implements Rule
{
    public function passes($attribute, $value)
    {
        // Normalize input: remove leading 'www.' if present
        $baseDomain = ltrim($value, 'www.');

        // Domains to check: input as is + the alternative (with or without 'www.')
        $domainsToCheck = [$baseDomain, 'www.' . $baseDomain];

        foreach ($domainsToCheck as $domain) {
            $records = dns_get_record($domain, DNS_CNAME);

            foreach ($records as $record) {
                if (isset($record['target']) && str_ends_with($record['target'], 'nsales.io')) {
                    return true;
                }
            }
        }

        return false; // Invalid if no matching CNAME found
    }

    public function message()
    {
        return 'The :attribute must have a CNAME pointing to nsales.io';
    }
}
