<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotionOutputRequest extends FormRequest
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
            'article_id' => [
                'required',
                'integer',
                'exists:articles,id',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'article_id.required' => 'ARTICLE_ID_REQUIRED',
            'article_id.integer' => 'ARTICLE_ID_MUST_BE_INTEGER',
            'article_id.exists' => 'ARTICLE_NOT_FOUND',
        ];
    }
}
