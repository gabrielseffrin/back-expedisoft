<?php

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;

class DockIntegrationRequest extends FormRequest
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
            'source_system' => 'required|string',
            'dock' => 'required|array',
            'dock.external_id' => 'required|string',
            'dock.dock_code' => 'required|string',
            'dock.description' => 'nullable|string',
            'dock.location' => 'nullable|string',
        ];
    }
}
