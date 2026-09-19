<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Customer is identified by email. If the email is new, `name`
            // is required to create the customer record on the fly.
            'customer_email' => ['required', 'email'],
            'customer_name' => ['required_if:customer_is_new,true', 'nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Runs after basic rules pass. We only know whether the customer is
     * "new" once we've checked the DB, so we inject that flag before
     * validation runs the required_if rule above.
     */
    protected function prepareForValidation(): void
    {
        $exists = $this->filled('customer_email')
            && \App\Models\Customer::where('email', $this->input('customer_email'))->exists();

        $this->merge(['customer_is_new' => ! $exists]);
    }

    public function messages(): array
    {
        return [
            'items.required' => 'An order must contain at least one product line.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}
