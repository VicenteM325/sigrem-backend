<?php

namespace App\Http\Controllers\Api\ResiduosApi;

use App\Repositories\TipoResiduoRepository;
use App\DTOs\ResiduosDTOs\TipoResiduoDTO;
USE App\Http\Controllers\Api\ApiController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TipoResiduoController extends ApiController
{
    public function __construct(
        private TipoResiduoRepository $tipoResiduoRepository
    ) {}

    /**
     * Listar todos los tipos de residuo
     * Requiere: rutas.ver
     * GET /api/tipos-residuo
     */
    public function index(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $tipos = $this->tipoResiduoRepository->all();
            
            $data = $tipos->map(fn($tipo) => TipoResiduoDTO::fromModel($tipo)->toResponseArray());
            
            return $this->successResponse(
                ['tipos_residuo' => $data],
                'Tipos de residuo obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener tipos de residuo: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Obtener tipos de residuo para selector
     * Requiere: rutas.ver
     * GET /api/tipos-residuo/select
     */
    public function forSelect(): JsonResponse
    {
        $this->authorizePermission('rutas.ver');

        try {
            $tipos = $this->tipoResiduoRepository->all();
            
            $data = $tipos->map(fn($tipo) => [
                'value' => $tipo->id_tipo_residuo,
                'label' => $tipo->nombre
            ]);
            
            return $this->successResponse(
                ['tipos' => $data],
                'Tipos de residuo obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener tipos de residuo: ' . $e->getMessage(),
                500
            );
        }
    }
}