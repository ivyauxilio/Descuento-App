<?php

namespace App\Http\Requests\Merchant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'merchant';
    }

    public function rules(): array
    {
        $menuItemId = $this->route('menu_item');

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:999999.99',
            // 'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            // 'stock_status' => ['required', Rule::in(['in_stock', 'low_stock', 'out_of_stock'])],
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            // 'is_featured' => 'nullable|boolean',
            // 'preparation_time' => 'nullable|integer|min:0',
            // 'ingredients' => 'nullable|array',
            // 'variants' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Menu item name is required.',
            'name.max' => 'Menu item name cannot exceed 255 characters.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.',
            // 'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'image.max' => 'The image size must not exceed 2MB.',
            'stock_quantity.integer' => 'Stock quantity must be a whole number.',
            'low_stock_threshold.integer' => 'Low stock threshold must be a whole number.',
        ];
    }

        protected function prepareForValidation(): void
    {
        if ($this->has('price')) {
            $this->merge([
                'price' => str_replace(',', '', $this->price),
            ]);
        }

        if ($this->has('is_featured')) {
            $this->merge([
                'is_featured' => filter_var($this->is_featured, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Set default values
        if (!$this->has('stock_quantity')) {
            $this->merge([
                'stock_quantity' => 0,
            ]);
        }

        if (!$this->has('low_stock_threshold')) {
            $this->merge([
                'low_stock_threshold' => 5,
            ]);
        }
    }

}