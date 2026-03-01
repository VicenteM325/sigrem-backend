<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'direccion' => 'nullable|string',

            'role' => 'required|exists:roles,name',

            'telefono' => 'nullable|string',

            'licencia' => 'required_if:role,conductor|string'
        ];
    }
}
