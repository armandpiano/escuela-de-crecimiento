<?php
/**
 * =============================================================================
 * EXCEPCIONES DE EMAIL
 * Christian LMS System - Infrastructure
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Mail\Exceptions;

use Exception;

/**
 * Excepción de Email
 */
class EmailException extends Exception
{
    /**
     * Códigos de error personalizados
     */
    const SMTP_CONNECTION_FAILED = 2001;
    const AUTHENTICATION_FAILED = 2002;
    const SEND_FAILED = 2003;
    const INVALID_RECIPIENT = 2004;
    const TEMPLATE_NOT_FOUND = 2005;

    protected $context = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Agregar contexto adicional
     */
    public function addContext(string $key, $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Obtener contexto
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Crear excepción de conexión SMTP fallida
     */
    public static function smtpConnectionFailed(string $host, int $port, Exception $previous = null): self
    {
        return new self(
            "No se pudo conectar al servidor SMTP {$host}:{$port}",
            self::SMTP_CONNECTION_FAILED,
            $previous,
            ['host' => $host, 'port' => $port]
        );
    }

    /**
     * Crear excepción de autenticación fallida
     */
    public static function authenticationFailed(string $username, Exception $previous = null): self
    {
        return new self(
            "Autenticación SMTP fallida para el usuario {$username}",
            self::AUTHENTICATION_FAILED,
            $previous,
            ['username' => $username]
        );
    }

    /**
     * Crear excepción de envío fallido
     */
    public static function sendFailed(string $to, string $subject, Exception $previous = null): self
    {
        return new self(
            "Error enviando email a {$to} con asunto: {$subject}",
            self::SEND_FAILED,
            $previous,
            ['to' => $to, 'subject' => $subject]
        );
    }
}
