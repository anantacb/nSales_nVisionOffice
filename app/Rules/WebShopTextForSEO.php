<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\HttpFoundation\ParameterBag;

class WebShopTextForSEO implements Rule
{
    protected $robots;
    protected $request;

    public function __construct(ParameterBag $request)
    {
        $this->robots = ['follow,index', 'nofollow,noindex'];
        $this->request = $request;
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
//        dd($attribute, $value);
//        dd($this->request->has('WebShopTexts'));
//        dd($this->request->get('ItemNumber'));
//        dd($this->request->get('WebShopTexts'));
        $index = explode('.', $attribute)[1];
        $type = $this->request->get('WebShopTexts')[$index]["Type"];
        if ($type === "SEORobot") {
            return in_array($value, $this->robots);
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This input value is invalid.';
    }

}
