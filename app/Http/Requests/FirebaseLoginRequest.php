<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FirebaseLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_token' => 'required|string|min:10',
        ];
    }

    public function messages(): array
    {
        return [
            'id_token.required' => 'Firebase token diperlukan',
            'id_token.string' => 'Token harus string',
            'id_token.min' => 'Token tidak valid',
        ];
    }
}
