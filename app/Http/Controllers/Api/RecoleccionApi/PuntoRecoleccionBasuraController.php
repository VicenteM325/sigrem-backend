<?php
namespace App\Http\Controllers\Api\RecoleccionApi;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\PuntoRecoleccionBasuraRepository;
use App\DTOs\RecoleccionDTOs\PuntoRecoleccionBasuraDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PuntoRecoleccionBasuraController extends ApiController
{
    public function __construct(
        private PuntoRecoleccionBasuraRepository $puntoRepository
    ) {}

    /**
     * Obtener puntos de una recolección
     */
    public function getByRecoleccion(int $idRecoleccion): JsonResponse
    {
        $this->authorizePermission('recoleccion.ver');

        try {
            $puntos = $this->puntoRepository->findByRecoleccion($idRecoleccion);

            $puntosData = $puntos->map(fn($punto) =>
                PuntoRecoleccionBasuraDTO::fromModel($punto)->toResponseArray()
            );

            return $this->successResponse(
                ['puntos' => $puntosData],
                'Puntos obtenidos correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener puntos: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Registrar basura en un punto
     */
    public function registrarBasura(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('recoleccion.finalizar');

        try {
                $request->validate([
                    'volumen_real_kg' => 'required|numeric|min:0',
                    'observaciones' => 'nullable|string|max:500'
                ]);

                $punto = $this->puntoRepository->find($id);
                if (!$punto) {
                    return $this->notFoundResponse('Punto no encontrado');
                }

                $punto->estado_recoleccion = 'recolectado';
                $punto->save();

                // Actualizar el total de la recolección sumando los volúmenes estimados de los puntos recolectados
                $this->actualizarTotalRecoleccion($punto->id_recoleccion);

                return $this->successResponse(
                    ['punto' => PuntoRecoleccionBasuraDTO::fromModel($punto)->toResponseArray()],
                    'Punto marcado como recolectado correctamente'
                );
            } catch (\Exception $e) {
                return $this->errorResponse('Error: ' . $e->getMessage(), 500);
            }
    }

    /**
     * Marcar punto como completado
     */
    public function completarPunto(int $id): JsonResponse
    {
        $this->authorizePermission('recoleccion.finalizar');

        try {
            $punto = $this->puntoRepository->find($id);
            if (!$punto) {
                return $this->notFoundResponse('Punto no encontrado');
            }

            $punto->estado_recoleccion = 'recolectado';
            $punto->save();

            return $this->successResponse(
                ['punto' => PuntoRecoleccionBasuraDTO::fromModel($punto)->toResponseArray()],
                'Punto completado correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al completar punto: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Actualizar el total de basura en recoleccion
     */
    private function actualizarTotalRecoleccion(int $idRecoleccion): void
    {
        // Sumar los volúmenes estimados de los puntos recolectados
        $total = \App\Models\recoleccion\PuntoRecoleccionBasura::where('id_recoleccion', $idRecoleccion)
            ->where('estado_recoleccion', 'recolectado')
            ->sum('volumen_estimado_kg');

        $recoleccion = \App\Models\recoleccion\Recoleccion::find($idRecoleccion);
        if ($recoleccion) {
            $recoleccion->basura_recolectada_ton = $total / 1000;
            $recoleccion->save();
        }
    }

    /**
     * Reportar problema en un punto
     */
    public function reportarProblema(Request $request, int $id): JsonResponse
    {
        $this->authorizePermission('recoleccion.reportar-incidencia');

        try {
            $request->validate([
                'observaciones' => 'required|string|max:500'
            ]);

            $punto = $this->puntoRepository->find($id);
            if (!$punto) {
                return $this->notFoundResponse('Punto no encontrado');
            }

            $punto->estado_recoleccion = 'problema';
            $punto->observaciones = $request->observaciones;
            $punto->save();

            return $this->successResponse(
                ['punto' => PuntoRecoleccionBasuraDTO::fromModel($punto)->toResponseArray()],
                'Problema reportado correctamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al reportar problema: ' . $e->getMessage(),
                500
            );
        }
    }
}
