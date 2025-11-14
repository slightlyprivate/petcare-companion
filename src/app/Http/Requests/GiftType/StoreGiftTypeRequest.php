<?php

namespace App\Http\Requests\GiftType;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreGiftTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:gift_types,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon_emoji' => ['required', 'string', 'max:10'],
            'color_code' => ['required', 'regex:/^#[0-9A-F]{6}$/i'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Gift type name is required.',
            'name.unique' => 'A gift type with this name already exists.',
            'icon_emoji.required' => 'Icon emoji is required.',
            'color_code.required' => 'Color code is required.',
            'color_code.regex' => 'Color code must be a valid hex color (e.g., #FF6B6B).',
        ];
    }
}
