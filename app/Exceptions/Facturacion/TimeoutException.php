<?php

namespace App\Exceptions\Facturacion;

/**
 * Excepción para errores de timeout en la API
 */
class TimeoutException extends ApiException
{
    public function __construct(
        string $message = 'La petición al servidor de facturación ha expirado',
        array $details = [],
        \Exception $previous = null
    ) {
        parent::__construct(
            $message,
            408, // Request Timeout
            'TIMEOUT_ERROR',
            $details,
            true, // Es reintentable
            $previous
        );
    }
}