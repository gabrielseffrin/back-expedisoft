<?php

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;

class UserIntegrationRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'source_system' => ['required', 'string', 'max:50'],

            'user' => ['required', 'array'],
            'user.external_id' => ['required', 'string', 'max:100'],
            'user.name' => ['required', 'string', 'max:255'],
            'user.email' => ['nullable', 'email', 'max:255']
        ];
    }

    public function messages(): array
    {
        return [
            'source_system.required' => 'O campo source_system é obrigatório.',
            'source_system.string' => 'O campo source_system deve ser uma string.',
            'source_system.max' => 'O campo source_system deve ter no máximo 50 caracteres.',

            'user.required' => 'O campo user é obrigatório.',
            'user.array' => 'O campo user deve ser um array.',

            'user.external_id.required' => 'O campo user.external_id é obrigatório.',
            'user.external_id.string' => 'O campo user.external_id deve ser uma string.',
            'user.external_id.max' => 'O campo user.external_id deve ter no máximo 100 caracteres.',

            'user.name.required' => 'O campo user.name é obrigatório.',
            'user.name.string' => 'O campo user.name deve ser uma string.',
            'user.name.max' => 'O campo user.name deve ter no máximo 255 caracteres.',

            'user.email.email' => 'O campo user.email deve ser um endereço de email válido.',
            'user.email.max' => 'O campo user.email deve ter no máximo 255 caracteres.'
        ];
    }
}
