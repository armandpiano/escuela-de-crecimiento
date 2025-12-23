<?php
/**
 * =============================================================================
 * EXCEPCIONES DE BASE DE DATOS
 * Christian LMS System - Infraestructura
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Persistence\Database\Exceptions;

use Exception;

/**
 * Excepción de Conexión a Base de Datos
 */
class ConnectionException extends Exception
{
    /**
     * Códigos de error personalizados
     */
    const CONNECTION_FAILED = 1001;
    const QUERY_EXECUTION_FAILED = 1002;
    const TRANSACTION_FAILED = 1003;
    const MIGRATION_FAILED = 1004;
    const SCHEMA_VALIDATION_FAILED = 1005;

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
     * Crear excepción de conexión fallida
     */
    public static function connectionFailed(string $host, string $database, string $username): self
    {
        return new self(
            "No se pudo conectar a la base de datos {$database} en {$host} con usuario {$username}",
            self::CONNECTION_FAILED,
            null,
            ['host' => $host, 'database' => $database, 'username' => $username]
        );
    }

    /**
     * Crear excepción de query fallida
     */
    public static function queryFailed(string $sql, array $params = [], Exception $previous = null): self
    {
        return new self(
            "Error ejecutando query SQL",
            self::QUERY_EXECUTION_FAILED,
            $previous,
            ['sql' => $sql, 'params' => $params]
        );
    }

    /**
     * Crear excepción de transacción
     */
    public static function transactionFailed(string $operation, Exception $previous = null): self
    {
        return new self(
            "Error en operación de transacción: {$operation}",
            self::TRANSACTION_FAILED,
            $previous,
            ['operation' => $operation]
        );
    }
}
