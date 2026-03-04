<?php

namespace App\DTOs;

class UserDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombres,
        public readonly string $apellidos,
        public readonly string $email,
        public readonly ?string $telefono,
        public readonly ?string $direccion,
        public readonly bool $estado,
        public readonly array $roles = [],
        public readonly ?ProfileDTO $perfil = null,
        public readonly ?string $password = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nombres: $data['nombres'],
            apellidos: $data['apellidos'],
            email: $data['email'],
            telefono: $data['telefono'] ?? null,
            direccion: $data['direccion'] ?? null,
            estado: $data['estado'] ?? true,
            roles: $data['roles'] ?? [],
            password: $data['password'] ?? null
        );
    }

    public function toArray(): array
    {
        $data = [
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'estado' => $this->estado,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        return $data;
    }
}