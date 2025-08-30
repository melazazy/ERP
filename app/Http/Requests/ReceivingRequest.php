<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceivingRequest extends FormRequest
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
            'receiving_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('receivings', 'receiving_number')->ignore($this->receiving_id)
            ],
            'supplier_id' => 'required|exists:suppliers,id',
            'department_id' => 'required|exists:departments,id',
            'date' => 'required|date|before_or_equal:today',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_rate' => 'nullable|numeric|min:0|max:100',
            'apply_tax' => 'boolean',
            'apply_discount' => 'boolean',
            'create_requisition' => 'boolean',
            'selected_items' => 'required|array|min:1',
            'selected_items.*.id' => 'required|exists:items,id',
            'selected_items.*.quantity' => 'required|numeric|min:0.01',
            'selected_items.*.unit_price' => 'required|numeric|min:0',
            'selected_items.*.unit_id' => 'required|exists:units,id',
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
            'receiving_number.required' => 'Receiving number is required.',
            'receiving_number.unique' => 'Receiving number already exists.',
            'supplier_id.required' => 'Supplier is required.',
            'supplier_id.exists' => 'Selected supplier is invalid.',
            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'Selected department is invalid.',
            'date.required' => 'Date is required.',
            'date.before_or_equal' => 'Date cannot be in the future.',
            'tax_rate.numeric' => 'Tax rate must be a number.',
            'tax_rate.min' => 'Tax rate cannot be negative.',
            'tax_rate.max' => 'Tax rate cannot exceed 100%.',
            'discount_rate.numeric' => 'Discount rate must be a number.',
            'discount_rate.min' => 'Discount rate cannot be negative.',
            'discount_rate.max' => 'Discount rate cannot exceed 100%.',
            'selected_items.required' => 'Please add at least one item.',
            'selected_items.min' => 'Please add at least one item.',
            'selected_items.*.id.required' => 'Item is required.',
            'selected_items.*.id.exists' => 'Selected item is invalid.',
            'selected_items.*.quantity.required' => 'Quantity is required.',
            'selected_items.*.quantity.numeric' => 'Quantity must be a number.',
            'selected_items.*.quantity.min' => 'Quantity must be greater than 0.',
            'selected_items.*.unit_price.required' => 'Unit price is required.',
            'selected_items.*.unit_price.numeric' => 'Unit price must be a number.',
            'selected_items.*.unit_price.min' => 'Unit price cannot be negative.',
            'selected_items.*.unit_id.required' => 'Unit is required.',
            'selected_items.*.unit_id.exists' => 'Selected unit is invalid.',
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
            'receiving_number' => 'receiving number',
            'supplier_id' => 'supplier',
            'department_id' => 'department',
            'date' => 'date',
            'tax_rate' => 'tax rate',
            'discount_rate' => 'discount rate',
            'selected_items' => 'items',
            'selected_items.*.id' => 'item',
            'selected_items.*.quantity' => 'quantity',
            'selected_items.*.unit_price' => 'unit price',
            'selected_items.*.unit_id' => 'unit',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert boolean strings to actual booleans
        $this->merge([
            'apply_tax' => filter_var($this->apply_tax, FILTER_VALIDATE_BOOLEAN),
            'apply_discount' => filter_var($this->apply_discount, FILTER_VALIDATE_BOOLEAN),
            'create_requisition' => filter_var($this->create_requisition, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
