<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'string|exists:roles,name', 
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'fecha_vencimiento_licencia' => 'required_if:roles.*,conductor|date',
            'categoria_licencia' => 'required_if:roles.*,conductor|string|in:A,B,C,D,E',
            'licencia' => 'required_if:roles.*,conductor|string|max:50', 
        ];
    }

    public function messages(): array
    {
        return [
            'licencia.required_if' => 'La licencia es obligatoria para conductores',
            'email.unique' => 'Este email ya está registrado',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'fecha_vencimiento_licencia.required_if' => 'La fecha de vencimiento es obligatoria para conductores',
            'categoria_licencia.required_if' => 'La categoría de licencia es obligatoria para conductores',
            'roles.required' => 'Debe seleccionar al menos un rol',
        ];
    }
}