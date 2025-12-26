<?php
/**
 * =============================================================================
 * ADMINISTRADOR DE CONEXIONES DE BASE DE DATOS
 * Christian LMS System - Infraestructura
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Persistence\Database;

use PDO;
use PDOException;
use ChristianLMS\Infrastructure\Persistence\Database\Exceptions\ConnectionException;

/**
 * Administrador de Conexiones de Base de Datos
 * 
 * Gestiona las conexiones a la base de datos principal, replicas de lectura
 * y master de escritura según el patrón Read/Write.
 */
class ConnectionManager
{
    /** @var array */
    private $config;
    /** @var PDO|null */
    private $mainConnection= null;
    /** @var PDO|null */
    private $readConnection= null;
    /** @var PDO|null */
    private $writeConnection= null;
    /** @var array */
    private $connectionPool= [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Obtener conexión principal
     */
    public function getConnection(string $type = 'default'): PDO
    {
        switch ($type) {
            case 'read':
                return $this->getReadConnection();
            case 'write':
                return $this->getWriteConnection();
            default:
                return $this->getMainConnection();
        }
    }

    /**
     * Obtener conexión principal (default)
     */
    private function getMainConnection(): PDO
    {
        if ($this->mainConnection === null) {
            $this->mainConnection = $this->createConnection($this->config['connections']['mysql']);
        }
        
        return $this->mainConnection;
    }

    /**
     * Obtener conexión de lectura (replica)
     */
    private function getReadConnection(): PDO
    {
        if ($this->readConnection === null) {
            $readConfig = $this->config['connections']['mysql_read'] ?? $this->config['connections']['mysql'];
            $this->readConnection = $this->createConnection($readConfig);
        }
        
        return $this->readConnection;
    }

    /**
     * Obtener conexión de escritura (master)
     */
    private function getWriteConnection(): PDO
    {
        if ($this->writeConnection === null) {
            $writeConfig = $this->config['connections']['mysql_write'] ?? $this->config['connections']['mysql'];
            $this->writeConnection = $this->createConnection($writeConfig);
        }
        
        return $this->writeConnection;
    }

    /**
     * Crear nueva conexión PDO
     */
    private function createConnection(array $config): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            
            // Configuraciones adicionales
            $pdo->exec("SET time_zone = '-06:00'"); // Zona horaria de México
            $pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
            return $pdo;
            
        } catch (PDOException $e) {
            throw new ConnectionException(
                "Error de conexión a base de datos: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Ejecutar query con selección automática de conexión
     */
    public function query(string $sql, array $params = [], string $type = 'read'): \PDOStatement
    {
        $connection = $this->getConnection($type);
        
        try {
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
            
        } catch (PDOException $e) {
            throw new ConnectionException(
                "Error ejecutando query: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Ejecutar query de lectura
     */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params, 'read');
        return $stmt->fetchAll();
    }

    /**
     * Ejecutar query de escritura
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->query($sql, $params, 'write');
        return $stmt !== false;
    }

    /**
     * Insertar y obtener ID
     */
    public function insert(string $sql, array $params = []): string
    {
        $stmt = $this->query($sql, $params, 'write');
        return $this->getWriteConnection()->lastInsertId();
    }

    /**
     * Obtener una sola fila
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params, 'read');
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction(): bool
    {
        return $this->getWriteConnection()->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit(): bool
    {
        return $this->getWriteConnection()->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback(): bool
    {
        return $this->getWriteConnection()->rollback();
    }

    /**
     * Verificar si hay transacción activa
     */
    public function inTransaction(): bool
    {
        return $this->getWriteConnection()->inTransaction();
    }

    /**
     * Obtener configuración de pool
     */
    public function getPoolConfig(): array
    {
        return $this->config['pool'] ?? [];
    }

    /**
     * Cerrar todas las conexiones
     */
    public function closeConnections(): void
    {
        $this->mainConnection = null;
        $this->readConnection = null;
        $this->writeConnection = null;
        $this->connectionPool = [];
    }

    /**
     * Verificar estado de conexiones
     */
    public function getConnectionStatus(): array
    {
        return [
            'main' => $this->mainConnection !== null,
            'read' => $this->readConnection !== null,
            'write' => $this->writeConnection !== null,
            'pool_size' => count($this->connectionPool)
        ];
    }
}
