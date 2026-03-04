<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function index(): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        
        return response()->json(['users' => $users]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $userDTO = UserDTO::fromRequest($request->validated());
            
            $profileData = array_merge(
                $request->only(['licencia', 'fecha_vencimiento_licencia', 'categoria_licencia', 'disponible']),
                $request->only(['preferencias'])
            );
            
            $user = $this->userService->createUser($userDTO, $profileData);
            
            return response()->json([
                'message' => 'Usuario creado correctamente',
                'user' => $user
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);
            
            return response()->json(['user' => $user]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        try {
            $userDTO = UserDTO::fromRequest($request->validated());
            
            $profileData = array_merge(
                $request->only(['licencia', 'fecha_vencimiento_licencia', 'categoria_licencia', 'disponible']),
                $request->only(['puntos_acumulados', 'nivel', 'logros', 'preferencias'])
            );
            
            $user = $this->userService->updateUser($id, $userDTO, $profileData);
            
            return response()->json([
                'message' => 'Usuario actualizado correctamente',
                'user' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);
            
            return response()->json([
                'message' => 'Usuario eliminado correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}