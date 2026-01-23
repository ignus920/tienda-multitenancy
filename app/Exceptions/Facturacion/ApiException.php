<?php

namespace App\Exceptions\Facturacion;

use Exception;
use Illuminate\Http\Response;

/**
 * Excepción base para errores de la API de facturación
 */
class ApiException extends Exception
{
    protected $statusCode;
    protected $errorCode;
    protected $details;
    protected $retryable;

    public function __construct(
        string $message = '',
        int $statusCode = 500,
        string $errorCode = 'API_ERROR',
        array $details = [],
        bool $retryable = false,
        Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->details = $details;
        $this->retryable = $retryable;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function isRetryable(): bool
    {
        return $this->retryable;
    }

    public function getUserMessage(): string
    {
        return $this->message;
    }

    public function toArray(): array
    {
        return [
            'error_code' => $this->errorCode,
            'message' => $this->message,
            'status_code' => $this->statusCode,
            'details' => $this->details,
            'retryable' => $this->retryable,
        ];
    }
}