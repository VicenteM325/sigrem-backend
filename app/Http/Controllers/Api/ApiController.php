<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

abstract class ApiController extends Controller
{
    protected function successResponse($data = null, string $message = 'Operación exitosa', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse(string $message, int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function notFoundResponse(string $message = 'Recurso no encontrado'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    protected function unauthorizedResponse(string $message = 'No autorizado'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    protected function validationErrorResponse($errors, string $message = 'Error de validación'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    protected function checkPermission(string $permission): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Super-admin tiene todos los permisos
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return $user->can($permission);
    }

    protected function authorizePermission(string $permission): void
    {
        if (!$this->checkPermission($permission)) {
            abort(403, 'No tienes permiso para realizar esta acción');
        }
    }
}