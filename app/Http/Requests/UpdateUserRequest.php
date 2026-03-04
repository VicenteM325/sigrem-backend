<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
{
    $userId = $this->route('id');

    return [
        'nombres' => 'required|string|max:255',
        'apellidos' => 'required|string|max:255',
        'email' => [
            'required',
            'email',
            Rule::unique('users')->ignore($userId)
        ],
        'password' => 'nullable|string|min:8|confirmed',
        'role' => 'sometimes|string|exists:roles,name',
        'roles' => 'sometimes|array', 
        'roles.*' => 'string|exists:roles,name',
        'telefono' => 'nullable|string|max:20',
        'direccion' => 'nullable|string|max:255',
        'licencia' => 'required_if:role,conductor|nullable|string|max:50',
        'estado' => 'sometimes|boolean',
    ];
}

protected function prepareForValidation()
{
    // Si viene 'roles' como array y no viene 'role', usar el primer rol
    if ($this->has('roles') && is_array($this->roles) && count($this->roles) > 0 && !$this->has('role')) {
        $this->merge([
            'role' => $this->roles[0]
        ]);
    }
}

    public function messages(): array
    {
        return [
            'licencia.required_if' => 'La licencia es obligatoria para conductores',
            'email.unique' => 'Este email ya está registrado',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ];
    }
}