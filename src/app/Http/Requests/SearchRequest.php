<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
             'page' => [
                'nullable', 
                'integer',
            ],
            'keyword' => [
                'nullable',
                'string',
                'min: 1',
            ],
            'category' => [
                'nullable',
                'string',
                Rule::in(['all', ...Category::pluck('slug')->toArray()]),
            ],
            'sort' => [
                'nullable',
                'string',
                Rule::in(['postDate', 'like']),
            ],
        ];
    }
}
