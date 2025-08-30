<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequisitionRequest extends FormRequest
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
            'requisition_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('requisitions', 'requisition_number')->ignore($this->requisition_id)
            ],
            'department_id' => 'required|exists:departments,id',
            'requested_by' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,approved,rejected',
            'requested_date' => 'nullable|date|before_or_equal:today',
            'selected_items' => 'required|array|min:1',
            'selected_items.*.id' => 'required|exists:items,id',
            'selected_items.*.quantity' => 'required|numeric|min:0.01',
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
            'requisition_number.required' => 'Requisition number is required.',
            'requisition_number.unique' => 'Requisition number already exists.',
            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'Selected department is invalid.',
            'requested_by.exists' => 'Selected user is invalid.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be pending, approved, or rejected.',
            'requested_date.date' => 'Requested date must be a valid date.',
            'requested_date.before_or_equal' => 'Requested date cannot be in the future.',
            'selected_items.required' => 'Please add at least one item.',
            'selected_items.min' => 'Please add at least one item.',
            'selected_items.*.id.required' => 'Item is required.',
            'selected_items.*.id.exists' => 'Selected item is invalid.',
            'selected_items.*.quantity.required' => 'Quantity is required.',
            'selected_items.*.quantity.numeric' => 'Quantity must be a number.',
            'selected_items.*.quantity.min' => 'Quantity must be greater than 0.',
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
            'requisition_number' => 'requisition number',
            'department_id' => 'department',
            'requested_by' => 'requested by',
            'status' => 'status',
            'requested_date' => 'requested date',
            'selected_items' => 'items',
            'selected_items.*.id' => 'item',
            'selected_items.*.quantity' => 'quantity',
            'selected_items.*.unit_id' => 'unit',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values if not provided
        if (!$this->requested_date) {
            $this->merge(['requested_date' => now()->toDateString()]);
        }

        if (!$this->status) {
            $this->merge(['status' => 'pending']);
        }
    }
}
