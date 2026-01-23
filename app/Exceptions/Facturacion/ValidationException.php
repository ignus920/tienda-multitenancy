<?php

namespace App\Exceptions\Facturacion;

/**
 * Excepción para errores de validación de datos
 */
class ValidationException extends ApiException
{
    public function __construct(
        string $message = 'Los datos enviados no son válidos',
        array $validationErrors = [],
        \Exception $previous = null
    ) {
        parent::__construct(
            $message,
            422, // Unprocessable Entity
            'VALIDATION_ERROR',
            ['validation_errors' => $validationErrors],
            false, // No es reintentable
            $previous
        );
    }

    public function getValidationErrors(): array
    {
        return $this->details['validation_errors'] ?? [];
    }
}