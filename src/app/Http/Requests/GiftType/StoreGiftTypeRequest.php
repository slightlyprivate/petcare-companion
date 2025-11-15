<?php

namespace App\Http\Requests\GiftType;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for storing a new gift type.
 *
 * @group Gift Types
 */
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
            'cost_in_credits' => ['nullable', 'integer', 'min:10', 'max:1000000'],
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
            'cost_in_credits.integer' => 'Cost must be an integer number of credits.',
            'cost_in_credits.min' => 'Minimum cost is 10 credits.',
            'cost_in_credits.max' => 'Maximum cost is 1,000,000 credits.',
        ];
    }

    /**
     * Body parameters for API docs.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Display name of the gift type.',
                'example' => 'Toy',
            ],
            'description' => [
                'description' => 'Optional description.',
                'example' => 'Fun pet toys and accessories.',
            ],
            'icon_emoji' => [
                'description' => 'Emoji used as icon in UI.',
                'example' => '🧸',
            ],
            'color_code' => [
                'description' => 'Hex color for UI accents.',
                'example' => '#FF6B6B',
            ],
            'cost_in_credits' => [
                'description' => 'Default cost in credits for this gift type.',
                'example' => 100,
            ],
            'sort_order' => [
                'description' => 'Ordering index (lower shows first).',
                'example' => 10,
            ],
            'is_active' => [
                'description' => 'Whether type is visible to the public.',
                'example' => true,
            ],
        ];
    }
}
