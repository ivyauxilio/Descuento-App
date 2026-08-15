<?php

namespace App\Http\Requests\Merchant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'merchant';
    }

    public function rules(): array
    {
        $promotionId = $this->route('promotion');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'promo_type' => ['required', Rule::in(['percentage', 'fixed', 'bogo'])],
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'start_date' => $isUpdate 
                ? 'required|date'  // Just validate it's a valid date, not future
                : 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'status' => ['required', Rule::in(['active', 'inactive', 'expired'])],
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'total_usage_limit' => 'nullable|integer|min:1',
            'poster_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Promotion title is required.',
            'title.max' => 'Promotion title cannot exceed 255 characters.',
            'promo_type.required' => 'Please select a promotion type.',
            'promo_type.in' => 'Invalid promotion type selected.',
            'value.required' => 'Promotion value is required.',
            'value.numeric' => 'Value must be a number.',
            'value.min' => 'Value must be at least 0.',
            'start_date.required' => 'Start date is required.',
            'start_date' => $this->isMethod('POST') 
                ? 'required|date|after_or_equal:today'  // Create: future date required
                : 'required|date',  // Update: any valid date allowed
            'end_date.after_or_equal' => 'End date must be after or equal to the start date.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected.',
            'min_quantity.integer' => 'Minimum quantity must be a whole number.',
            'min_quantity.min' => 'Minimum quantity must be at least 1.',
            'min_order_amount.numeric' => 'Minimum order amount must be a number.',
            'usage_limit_per_user.integer' => 'Usage limit per user must be a whole number.',
            'total_usage_limit.integer' => 'Total usage limit must be a whole number.',
            'poster_image.image' => 'The poster must be an image file.',
            'poster_image.mimes' => 'The poster must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'poster_image.max' => 'The poster must not be larger than 5MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Clean up value
        if ($this->has('value')) {
            $this->merge([
                'value' => str_replace(',', '', $this->value),
            ]);
        }

        // Clean up min_order_amount
        if ($this->has('min_order_amount')) {
            $this->merge([
                'min_order_amount' => str_replace(',', '', $this->min_order_amount),
            ]);
        }
    }
}