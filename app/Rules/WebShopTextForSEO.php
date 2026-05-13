<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Symfony\Component\HttpFoundation\ParameterBag;

class WebShopTextForSEO implements ValidationRule
{
    protected array $robots;
    protected ParameterBag $request;

    public function __construct(ParameterBag $request)
    {
        $this->robots = ['follow,index', 'nofollow,noindex'];
        $this->request = $request;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $index = explode('.', $attribute)[1];
        $type = $this->request->get('WebShopTexts')[$index]["Type"];
        if ($type === "SEORobot" && !in_array($value, $this->robots)) {
            $fail('This input value is invalid.');
        }
    }
}
