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
}
