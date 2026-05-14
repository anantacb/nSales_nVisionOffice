<?php

namespace App\Services\Resolvers;

use Illuminate\Support\Facades\Cache;

class CompanyServiceResolver
{
    public static function resolveConcrete(string $baseConcrete): string
    {
        $domain = self::activeDomainName();
        if ($domain === null) {
            return $baseConcrete;
        }

        $pos = strrpos($baseConcrete, '\\');
        $namespace = substr($baseConcrete, 0, $pos);
        $shortName = substr($baseConcrete, $pos + 1);
        $candidate = "{$namespace}\\{$domain}\\{$shortName}";

        return class_exists($candidate) ? $candidate : $baseConcrete;
    }

    private static function activeDomainName(): ?string
    {
        $companyId = request()?->input('CompanyId');
        if (!$companyId) {
            return null;
        }

        $company = Cache::get('company_' . $companyId);
        return $company["DomainName"] ?? null;
    }
}
