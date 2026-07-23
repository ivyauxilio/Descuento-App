<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MerchantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        $merchantId = $this->route('merchant');

        return [
            'owner_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,category_id',
            'province_id' => 'required|exists:provinces,province_id',
            'business_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('merchants')->ignore($merchantId, 'merchant_id')
            ],
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'status' => ['required', Rule::in(['pending', 'approved', 'active', 'rejected', 'suspended'])],
        ];
    }

    public function messages(): array
    {
        return [
            'owner_id.required' => 'Please select an owner.',
            'owner_id.exists' => 'The selected owner does not exist.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'province_id.required' => 'Please select a province.',
            'province_id.exists' => 'The selected province is invalid.',
            'business_name.required' => 'Business name is required.',
            'business_name.max' => 'Business name cannot exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered to another merchant.',
            'street_address.required' => 'Street address is required.',
            'city.required' => 'City is required.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->email) {
            $this->merge([
                'email' => strtolower($this->email),
            ]);
        }

        if ($this->business_name) {
            $this->merge([
                'business_name' => trim($this->business_name),
            ]);
        }
    }
}