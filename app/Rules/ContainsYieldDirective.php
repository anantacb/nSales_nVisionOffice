<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ContainsYieldDirective implements Rule
{
    protected $template;

    /**
     * Create a new rule instance.
     *
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->template && str_contains($this->template, "@yield('content')");
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "The selected layout's template does not contain @yield('content').";
    }
}
