<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:bug,broken_asset,lag,inappropriate,review_abuse,other'],
            'description' => ['required', 'string', 'min:5', 'max:1000'],
            'game_review_id' => ['nullable', 'integer', 'exists:game_reviews,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Vui lòng chọn loại sự cố cần báo cáo.',
            'type.in' => 'Loại báo cáo không hợp lệ.',
            'description.required' => 'Vui lòng mô tả chi tiết lỗi hoặc vi phạm.',
            'description.min' => 'Mô tả báo cáo cần ít nhất 5 ký tự.',
            'description.max' => 'Mô tả báo cáo không vượt quá 1000 ký tự.',
        ];
    }
}

