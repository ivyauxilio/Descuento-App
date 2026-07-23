<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'merchant_id' => 'required|exists:merchants,merchant_id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => ['required', Rule::in(['available', 'unavailable', 'out_of_stock'])],
        ];
    }

    public function messages(): array
    {
        return [
            'merchant_id.required' => 'Please select a merchant.',
            'merchant_id.exists' => 'The selected merchant does not exist.',
            'name.required' => 'Menu item name is required.',
            'name.max' => 'Menu item name cannot exceed 255 characters.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'image.max' => 'The image size must not exceed 2MB.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Clean up price
        if ($this->has('price')) {
            $this->merge([
                'price' => str_replace(',', '', $this->price),
            ]);
        }
    }
}