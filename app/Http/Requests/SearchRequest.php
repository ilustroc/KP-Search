<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'documento' => trim((string) $this->input('documento', '')),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'documento' => ['required', 'string', 'max:8', 'regex:/\A[0-9]{8}\z/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'documento.required' => 'Ingrese un DNI válido de 8 dígitos.',
            'documento.max' => 'Ingrese un DNI válido de 8 dígitos.',
            'documento.regex' => 'Ingrese un DNI válido de 8 dígitos.',
        ];
    }
}
