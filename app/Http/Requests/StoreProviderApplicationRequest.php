<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Any authenticated user who does not yet have a provider profile may apply.
        // The controller enforces this; authorization here confirms authentication only.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string', 'max:2000'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
        ];
    }
}
