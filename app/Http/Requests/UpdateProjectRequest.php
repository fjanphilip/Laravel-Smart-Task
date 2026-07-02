<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'sometimes' artinya: jika input 'name' dikirim, maka wajib berupa string. Jika tidak dikirim, abaikan (lolos).
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            "status" => "required|in:active,archived,completed"
        ];
    }

    public function messages(): array
    {
        return [
            "name.required" => "Nama project wajib diisi.",
            "name.string" => "Nama project harus berupa string.",
            "name.max" => "Nama project tidak boleh lebih dari 255 karakter.",
            "description.required" => "Deskripsi project wajib diisi.",
            "description.string" => "Deskripsi project harus berupa string.",
            "description.max" => "Deskripsi project tidak boleh lebih dari 255 karakter.",
            "status.required" => "Status project wajib diisi.",
            "status.in" => "Status project harus berupa active, archived, atau completed.",
        ];
    }
}
