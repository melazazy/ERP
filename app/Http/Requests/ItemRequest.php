<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
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
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'name')->ignore($this->item_id)
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'code')->ignore($this->item_id)
            ],
            'subcategory_id' => 'required|exists:subcategories,id',
            'description' => 'nullable|string|max:1000',
            'unit_id' => 'nullable|exists:units,id',
            'minimum_stock' => 'nullable|numeric|min:0',
            'maximum_stock' => 'nullable|numeric|min:0|gt:minimum_stock',
            'reorder_point' => 'nullable|numeric|min:0',
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Item name is required.',
            'name.unique' => 'Item name already exists.',
            'code.required' => 'Item code is required.',
            'code.unique' => 'Item code already exists.',
            'subcategory_id.required' => 'Subcategory is required.',
            'subcategory_id.exists' => 'Selected subcategory is invalid.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'unit_id.exists' => 'Selected unit is invalid.',
            'minimum_stock.numeric' => 'Minimum stock must be a number.',
            'minimum_stock.min' => 'Minimum stock cannot be negative.',
            'maximum_stock.numeric' => 'Maximum stock must be a number.',
            'maximum_stock.min' => 'Maximum stock cannot be negative.',
            'maximum_stock.gt' => 'Maximum stock must be greater than minimum stock.',
            'reorder_point.numeric' => 'Reorder point must be a number.',
            'reorder_point.min' => 'Reorder point cannot be negative.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => 'item name',
            'code' => 'item code',
            'subcategory_id' => 'subcategory',
            'description' => 'description',
            'unit_id' => 'unit',
            'minimum_stock' => 'minimum stock',
            'maximum_stock' => 'maximum stock',
            'reorder_point' => 'reorder point',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from string fields
        $this->merge([
            'name' => trim($this->name),
            'code' => trim($this->code),
            'description' => $this->description ? trim($this->description) : null,
        ]);
    }
}
