<?php

namespace App\Http\Requests\GiftType;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for updating an existing gift type.
 *
 * @group Gift Types
 */
class UpdateGiftTypeRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('gift_types')->ignore($this->giftType)],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon_emoji' => ['sometimes', 'required', 'string', 'max:10'],
            'color_code' => ['sometimes', 'required', 'regex:/^#[0-9A-F]{6}$/i'],
            'cost_in_credits' => ['nullable', 'integer', 'min:10', 'max:1000000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
