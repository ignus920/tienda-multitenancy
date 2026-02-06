<?php

namespace App\Exceptions\Facturacion;

/**
 * Excepción para errores de autenticación con la API
 */
class AuthenticationException extends ApiException
{
    public function __construct(
        string $message = 'Error de autenticación con el servidor de facturación',
        array $details = [],
        \Exception $previous = null
    ) {
        parent::__construct(
            $message,
            401, // Unauthorized
            'AUTHENTICATION_ERROR',
            $details,
            false, // No es reintentable
            $previous
        );
    }
}