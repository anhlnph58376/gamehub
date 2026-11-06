<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Vui lòng chọn số sao đánh giá (1 - 5 sao).',
            'rating.min' => 'Số sao tối thiểu là 1.',
            'rating.max' => 'Số sao tối đa là 5.',
            'comment.max' => 'Nội dung nhận xét không được vượt quá 1000 ký tự.',
        ];
    }
}

