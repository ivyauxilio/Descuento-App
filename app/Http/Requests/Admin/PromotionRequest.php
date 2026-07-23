<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromotionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $promotionId = $this->route('promotion');

        return [
            'merchant_id' => 'required|exists:merchants,merchant_id',
            'category_id' => 'nullable|exists:categories,category_id',
            'free_menu_item_id' => 'nullable|exists:menu_items,menu_item_id',
            'required_menu_item_id' => 'nullable|exists:menu_items,menu_item_id',
            'title' => 'required|string|max:255',
            'promo_type' => ['required', Rule::in(['percentage', 'fixed', 'bogo'])],
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => ['required', Rule::in(['active', 'inactive', 'expired'])],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'merchant_id.required' => 'Please select a merchant.',
            'merchant_id.exists' => 'The selected merchant does not exist.',
            'title.required' => 'Promotion title is required.',
            'title.max' => 'Promotion title cannot exceed 255 characters.',
            'promo_type.required' => 'Please select a promotion type.',
            'promo_type.in' => 'Invalid promotion type selected.',
            'value.required' => 'Promotion value is required.',
            'value.numeric' => 'Value must be a number.',
            'value.min' => 'Value must be at least 0.',
            'min_order_amount.numeric' => 'Minimum order amount must be a number.',
            'min_order_amount.min' => 'Minimum order amount must be at least 0.',
            'min_quantity.integer' => 'Minimum quantity must be a whole number.',
            'min_quantity.min' => 'Minimum quantity must be at least 1.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Please enter a valid date.',
            'start_date.after_or_equal' => 'Start date must be today or a future date.',
            'end_date.date' => 'Please enter a valid date.',
            'end_date.after_or_equal' => 'End date must be after or equal to the start date.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
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

        // Set default for BOGO value
        if ($this->promo_type === 'bogo' && empty($this->value)) {
            $this->merge([
                'value' => 0,
            ]);
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function attributes(): array
    {
        return [
            'merchant_id' => 'merchant',
            'category_id' => 'category',
            'free_menu_item_id' => 'free menu item',
            'required_menu_item_id' => 'required menu item',
            'title' => 'title',
            'promo_type' => 'promotion type',
            'value' => 'value',
            'min_order_amount' => 'minimum order amount',
            'min_quantity' => 'minimum quantity',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'status' => 'status',
        ];
    }
}