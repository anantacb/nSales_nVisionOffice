<?php

namespace App\Http\Requests\WebShopText;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrCreateByItem extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'ItemId' => 'required',
            'ItemNumber' => 'required',
            'WebShopTexts' => 'required|array',
            'WebShopTexts.*.Id' => 'nullable',
            'WebShopTexts.*.Type' => 'required',
            'WebShopTexts.*.Language' => 'required',
            'WebShopTexts.*.ElementType' => 'required|in:Item',
//            'WebShopTexts.*.Text' => [
//                new WebShopTextForSEO($this->request)
//            ],
        ];
    }
}
