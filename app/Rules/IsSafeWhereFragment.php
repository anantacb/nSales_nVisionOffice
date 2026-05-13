<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use PHPSQLParser\PHPSQLParser;

class IsSafeWhereFragment implements ValidationRule
{
    private const ALLOWED_EXPR_TYPES = [
        'colref',
        'const',
        'operator',
        'expression',
        'bracket_expression',
        'in-list',
        'sign',
    ];

    private const ALLOWED_FUNCTIONS = [
        'NOW', 'CURDATE', 'CURRENT_DATE', 'CURRENT_TIMESTAMP', 'CURTIME',
        'COALESCE', 'IFNULL', 'NULLIF', 'LOWER', 'UPPER', 'LENGTH', 'TRIM',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $normalized = $this->normalize((string) $value);

        try {
            $parsed = (new PHPSQLParser())->parse('SELECT 1 FROM dual WHERE (' . $normalized . ')');
        } catch (\Throwable $e) {
            $fail('The :attribute is not a valid SQL boolean expression.');
            return;
        }

        if (!is_array($parsed) || empty($parsed['WHERE'])) {
            $fail('The :attribute could not be parsed as a WHERE clause.');
            return;
        }

        foreach (array_keys($parsed) as $clause) {
            if (!in_array($clause, ['SELECT', 'FROM', 'WHERE'], true)) {
                $fail("The :attribute contains a disallowed SQL clause ({$clause}).");
                return;
            }
        }

        $this->walk($parsed['WHERE'], $fail);
    }

    private function normalize(string $value): string
    {
        $value = trim($value);

        if (strlen($value) >= 2 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }

        $value = preg_replace('/"\+\s[.\w]+\s\+"/', "'__placeholder__'", $value);

        if (preg_match('/^\s*(AND|OR)\b/i', $value)) {
            $value = '1 ' . $value;
        }

        return $value;
    }

    private function walk(array $nodes, Closure $fail): bool
    {
        foreach ($nodes as $node) {
            if (!is_array($node) || !isset($node['expr_type'])) {
                continue;
            }

            $type = $node['expr_type'];

            if (in_array($type, ['function', 'aggregate_function', 'custom_function'], true)) {
                $name = strtoupper((string) ($node['base_expr'] ?? ''));
                if (!in_array($name, self::ALLOWED_FUNCTIONS, true)) {
                    $fail("The :attribute uses a disallowed function ({$name}).");
                    return false;
                }
            } elseif (!in_array($type, self::ALLOWED_EXPR_TYPES, true)) {
                $fail("The :attribute contains a disallowed token type ({$type}).");
                return false;
            }

            if ($type === 'colref') {
                $parts = $node['no_quotes']['parts'] ?? null;
                if (is_array($parts) && count($parts) > 2) {
                    $fail('The :attribute may not reference schema-qualified columns.');
                    return false;
                }
            }

            if (!empty($node['sub_tree']) && is_array($node['sub_tree']) && !$this->walk($node['sub_tree'], $fail)) {
                return false;
            }
        }

        return true;
    }
}
