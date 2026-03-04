<?php
// app/Services/UserService.php

namespace App\Services;

use App\DTOs\UserDTO;
use App\DTOs\ProfileDTO;
use App\Repositories\UserRepository;
use App\Repositories\ConductorRepository;
use App\Repositories\CiudadanoRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private ConductorRepository $conductorRepository,
        private CiudadanoRepository $ciudadanoRepository
    ) {}

    public function findUserById(int $id): ?array
    {
        try {
            $user = $this->userRepository->findById($id);
            
            if (!$user) {
                return null;
            }
            
            return $this->formatUserResponse($user);
            
        } catch (\Exception $e) {
            \Log::error('Error en findUserById: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getAllUsers()
    {
        $users = $this->userRepository->getAllWithRelations();
        
        return $users->map(function ($user) {
            return $this->formatUserResponse($user);
        });
    }

    public function createUser(UserDTO $userDTO, array $profileData = [])
    {
        return DB::transaction(function () use ($userDTO, $profileData) {
            // Crear usuario
            $userData = $userDTO->toArray();
            $userData['password'] = Hash::make($userDTO->password);
            
            $user = $this->userRepository->create($userData);

            // Asignar roles
            if (!empty($userDTO->roles)) {
                $this->userRepository->syncRoles($user, $userDTO->roles);
            }

            // Crear perfiles según roles
            $this->createProfilesByRoles($user, $userDTO->roles, $profileData);

            return $this->formatUserResponse($user->load(['roles', 'conductor', 'ciudadano']));
        });
    }

    public function updateUser(int $id, UserDTO $userDTO, array $profileData = [])
    {
        return DB::transaction(function () use ($id, $userDTO, $profileData) {
            $user = $this->userRepository->findById($id);
            
            if (!$user) {
                throw new \Exception('Usuario no encontrado');
            }

            // Actualizar datos básicos
            $userData = $userDTO->toArray();
            if ($userDTO->password) {
                $userData['password'] = Hash::make($userDTO->password);
            }
            
            $user = $this->userRepository->update($user, $userData);

            // Actualizar roles si es necesario
            $rolesActuales = $user->roles->pluck('name')->toArray();
            if (!empty($userDTO->roles) && $rolesActuales !== $userDTO->roles) {
                $this->updateUserRoles($user, $rolesActuales, $userDTO->roles);
            }

            // Actualizar perfiles según roles ACTUALES
            $this->updateProfilesByRoles($user, $userDTO->roles ?? $rolesActuales, $profileData);

            return $this->formatUserResponse($user->fresh(['roles', 'conductor', 'ciudadano']));
        });
    }

    public function deleteUser(int $id): void
    {
        DB::transaction(function () use ($id) {
            $user = $this->userRepository->findById($id);
            
            if (!$user) {
                throw new \Exception('Usuario no encontrado');
            }

            // Eliminar perfiles relacionados
            if ($user->conductor) {
                $this->conductorRepository->delete($user->conductor);
            }
            if ($user->ciudadano) {
                $this->ciudadanoRepository->delete($user->ciudadano);
            }

            // Eliminar usuario
            $this->userRepository->delete($user);
        });
    }

    private function createProfilesByRoles(User $user, array $roles, array $profileData): void
    {
        if (in_array('conductor', $roles)) {
            $this->conductorRepository->create($user->id, [
                'licencia' => $profileData['licencia'] ?? 'LIC-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'fecha_vencimiento_licencia' => $profileData['fecha_vencimiento_licencia'] ?? now()->addYear(),
                'categoria_licencia' => $profileData['categoria_licencia'] ?? 'B',
                'disponible' => $profileData['disponible'] ?? true,
            ]);
        }

        if (in_array('ciudadano', $roles)) {
            $this->ciudadanoRepository->create($user->id, [
                'preferencias' => $profileData['preferencias'] ?? null,
            ]);
        }
    }

    private function updateProfilesByRoles(User $user, array $roles, array $profileData): void
    {
        if (in_array('conductor', $roles)) {
            if ($user->conductor) {
                $this->conductorRepository->update($user->conductor, array_intersect_key($profileData, array_flip([
                    'licencia', 'fecha_vencimiento_licencia', 'categoria_licencia', 'disponible'
                ])));
            } else {
                $this->conductorRepository->create($user->id, $profileData);
            }
        }

        if (in_array('ciudadano', $roles)) {
            if ($user->ciudadano) {
                $this->ciudadanoRepository->update($user->ciudadano, array_intersect_key($profileData, array_flip([
                    'puntos_acumulados', 'nivel', 'logros', 'preferencias'
                ])));
            } else {
                $this->ciudadanoRepository->create($user->id, $profileData);
            }
        }
    }

    private function updateUserRoles(User $user, array $rolesActuales, array $nuevosRoles): void
    {
        $this->userRepository->syncRoles($user, $nuevosRoles);

        // Limpiar perfiles que ya no corresponden
        if (in_array('conductor', $rolesActuales) && !in_array('conductor', $nuevosRoles) && $user->conductor) {
            $this->conductorRepository->delete($user->conductor);
        }

        if (in_array('ciudadano', $rolesActuales) && !in_array('ciudadano', $nuevosRoles) && $user->ciudadano) {
            $this->ciudadanoRepository->delete($user->ciudadano);
        }
    }

    private function formatUserResponse($user): array
    {
        $perfil = $user->conductor 
            ? ProfileDTO::fromConductor($user->conductor)->toArray()
            : ($user->ciudadano ? ProfileDTO::fromCiudadano($user->ciudadano)->toArray() : null);

        return [
            'id' => $user->id,
            'nombres' => $user->nombres,
            'apellidos' => $user->apellidos,
            'email' => $user->email,
            'telefono' => $user->telefono,
            'direccion' => $user->direccion,
            'estado' => $user->estado,
            'created_at' => $user->created_at ? $user->created_at->toISOString() : null,
            'updated_at' => $user->updated_at ? $user->updated_at->toISOString() : null,
            'roles' => $user->roles->pluck('name'),
            'perfil' => $perfil
        ];
    }
}