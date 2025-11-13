<?php

namespace App\Http\Requests\Pet;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for restoring a deleted pet.
 */
class RestorePetRequest extends FormRequest
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
        return [
            //
        ];
    }

    /**
     * Get the body parameters for this request.
     */
    public function bodyParameters(): array
    {
        return [];
    }
}
