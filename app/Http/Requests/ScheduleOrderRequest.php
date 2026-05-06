<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleOrderRequest extends FormRequest
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
            'id' => 'required|string',
            'scheduled_at' => 'required|date',
            'status' => 'required|string',
            'dock_id' => 'nullable|string',
            'operator_id' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The id field is required.',
            'id.string' => 'The id field must be a string.',
            'scheduled_at.required' => 'The scheduled_at field is required.',
            'scheduled_at.date' => 'The scheduled_at field must be a valid date.',
            'status.required' => 'The status field is required.',
            'status.string' => 'The status field must be a string.',
            'dock_id.string' => 'The dock_id field must be a string.',
            'operator_id.string' => 'The operator_id field must be a string.',
        ];
    }
}
