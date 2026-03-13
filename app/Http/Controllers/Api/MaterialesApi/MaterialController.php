<?php

namespace App\Http\Controllers\Api\MaterialesApi;

use App\Http\Controllers\Api\ApiController;
use App\Models\contenedores\TipoMaterial;
use Illuminate\Http\JsonResponse;

class MaterialController extends ApiController
{
    /**
     * Obtener materiales para selector
     * GET /api/materiales/select
     */
    public function forSelect(): JsonResponse
    {
        try {
            $materiales = TipoMaterial::orderBy('nombre_material')->get();

            $data = $materiales->map(fn($material) => [
                'value' => $material->id_material,
                'label' => $material->nombre_material
            ]);

            return $this->successResponse(
                ['materiales' => $data],
                'Materiales obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener materiales: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Listar todos los materiales
     * GET /api/materiales
     */
    public function index(): JsonResponse
    {
        try {
            $materiales = TipoMaterial::all();

            return $this->successResponse(
                ['materiales' => $materiales],
                'Materiales obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener materiales: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Crear nuevo material
     * POST /api/materiales
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizePermission('materiales.crear');

        try {
            $request->validate([
                'nombre_material' => 'required|string|max:50|unique:tipos_material,nombre_material',
                'descripcion' => 'nullable|string|max:255'
            ]);

            $material = TipoMaterial::create([
                'nombre_material' => $request->nombre_material,
                'descripcion' => $request->descripcion
            ]);

            return $this->successResponse(
                ['material' => $material],
                'Material creado correctamente',
                201
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error: ' . $e->getMessage(), 500);
        }
    }
}
