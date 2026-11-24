<?php

namespace App\Http\Requests;

use App\Models\WalkInFeedback;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWalkInFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_name' => ['nullable', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'visited_at' => ['required', 'date'],
            'recommend_score' => ['required', 'integer', 'min:0', 'max:10'],
            'rating_reason' => ['required', 'string', 'max:5000'],
            'satisfaction_level' => ['required', 'integer', Rule::in(array_keys(WalkInFeedback::SATISFACTION_LABELS))],
            'improvement_suggestions' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'recommend_score.required' => 'Please select how likely you are to recommend PPMZ.',
            'rating_reason.required' => 'Please tell us the reason for your rating.',
            'satisfaction_level.required' => 'Please select your overall satisfaction level.',
        ];
    }
}