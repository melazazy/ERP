<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseService
{
    /**
     * Execute a database transaction with error handling
     *
     * @param callable $callback
     * @param string $errorMessage
     * @return mixed
     * @throws Exception
     */
    protected function executeTransaction(callable $callback, string $errorMessage = 'Transaction failed')
    {
        try {
            DB::beginTransaction();
            $result = $callback();
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($errorMessage . ': ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Log service method execution
     *
     * @param string $method
     * @param array $parameters
     * @param mixed $result
     * @return void
     */
    protected function logExecution(string $method, array $parameters = [], $result = null): void
    {
        Log::info("Service method executed: " . static::class . "@{$method}", [
            'parameters' => $parameters,
            'result' => $result
        ]);
    }

    /**
     * Validate model exists or throw exception
     *
     * @param Model|null $model
     * @param string $message
     * @return Model
     * @throws Exception
     */
    protected function validateModelExists(?Model $model, string $message = 'Model not found'): Model
    {
        if (!$model) {
            throw new Exception($message);
        }
        return $model;
    }

    /**
     * Format error message for user display
     *
     * @param Exception $e
     * @param string $defaultMessage
     * @return string
     */
    protected function formatErrorMessage(Exception $e, string $defaultMessage = 'An error occurred'): string
    {
        if (config('app.debug')) {
            return $e->getMessage();
        }
        return $defaultMessage;
    }
}
