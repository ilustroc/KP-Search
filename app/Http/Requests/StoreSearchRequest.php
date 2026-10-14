<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('search.use') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document' => ['required', 'string', 'regex:/\A(?:\d{8}|\d{11})\z/'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Ingrese un DNI o RUC.',
            'document.regex' => 'El documento debe contener exactamente 8 dígitos para DNI u 11 dígitos para RUC.',
        ];
    }
}
