<?php

namespace App\Exceptions\Facturacion;

/**
 * Excepción para errores de conexión con la API
 */
class ConnectionException extends ApiException
{
    public function __construct(
        string $message = 'No se puede conectar al servidor de facturación',
        array $details = [],
        \Exception $previous = null
    ) {
        parent::__construct(
            $message,
            503, // Service Unavailable
            'CONNECTION_ERROR',
            $details,
            true, // Es reintentable
            $previous
        );
    }
}