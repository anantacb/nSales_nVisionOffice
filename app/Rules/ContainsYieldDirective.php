<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ContainsYieldDirective implements ValidationRule
{
    protected $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->template || !str_contains($this->template, "@yield('content')")) {
            $fail("The selected layout's template does not contain @yield('content').");
        }
    }
}
