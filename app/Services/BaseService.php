<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseService
{
    protected function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    protected function commitTransaction(): void
    {
        DB::commit();
    }

    protected function rollbackTransaction(): void
    {
        DB::rollBack();
    }

    protected function executeInTransaction(callable $callback)
    {
        try {
            $this->beginTransaction();
            $result = $callback();
            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            Log::error('Error en transacción: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }
}