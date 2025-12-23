<?php
/**
 * =============================================================================
 * ADMINISTRADOR DE ESQUEMA Y MIGRACIONES
 * Christian LMS System - Infraestructura
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Persistence\Database;

use ChristianLMS\Infrastructure\Persistence\Database\Exceptions\ConnectionException;

/**
 * Administrador de Esquema y Migraciones
 * 
 * Gestiona la estructura de la base de datos, migraciones y verificaciones
 * de integridad del esquema.
 */
class SchemaManager
{
    private ConnectionManager $connectionManager;

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * Crear tabla si no existe
     */
    public function createTable(string $tableName, array $columns, array $options = []): bool
    {
        $sql = $this->buildCreateTableSQL($tableName, $columns, $options);
        
        try {
            return $this->connectionManager->execute($sql);
        } catch (ConnectionException $e) {
            throw ConnectionException::queryFailed("CREATE TABLE {$tableName}", [], $e);
        }
    }

    /**
     * Modificar tabla existente
     */
    public function alterTable(string $tableName, array $modifications): bool
    {
        $sql = $this->buildAlterTableSQL($tableName, $modifications);
        
        try {
            return $this->connectionManager->execute($sql);
        } catch (ConnectionException $e) {
            throw ConnectionException::queryFailed("ALTER TABLE {$tableName}", [], $e);
        }
    }

    /**
     * Eliminar tabla
     */
    public function dropTable(string $tableName): bool
    {
        $sql = "DROP TABLE IF EXISTS {$tableName}";
        
        try {
            return $this->connectionManager->execute($sql);
        } catch (ConnectionException $e) {
            throw ConnectionException::queryFailed("DROP TABLE {$tableName}", [], $e);
        }
    }

    /**
     * Verificar si tabla existe
     */
    public function tableExists(string $tableName): bool
    {
        $sql = "SHOW TABLES LIKE ?";
        $result = $this->connectionManager->fetch($sql, [$tableName]);
        return !empty($result);
    }

    /**
     * Obtener columnas de una tabla
     */
    public function getTableColumns(string $tableName): array
    {
        $sql = "SHOW COLUMNS FROM {$tableName}";
        $columns = $this->connectionManager->select($sql);
        
        $result = [];
        foreach ($columns as $column) {
            $result[] = [
                'name' => $column['Field'],
                'type' => $column['Type'],
                'nullable' => $column['Null'] === 'YES',
                'default' => $column['Default'],
                'key' => $column['Key'],
                'extra' => $column['Extra']
            ];
        }
        
        return $result;
    }

    /**
     * Ejecutar migraciones pendientes
     */
    public function runPendingMigrations(string $migrationsPath): void
    {
        if (!is_dir($migrationsPath)) {
            return;
        }

        // Crear tabla de migraciones si no existe
        $this->createMigrationsTable();

        // Obtener migraciones ejecutadas
        $executedMigrations = $this->getExecutedMigrations();

        // Obtener archivos de migración
        $migrationFiles = glob($migrationsPath . '/*.php');
        sort($migrationFiles);

        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.php');
            
            if (!in_array($migrationName, $executedMigrations)) {
                $this->executeMigration($file, $migrationName);
            }
        }
    }

    /**
     * Crear tabla de migraciones
     */
    private function createMigrationsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
        ";
        
        $this->connectionManager->execute($sql);
    }

    /**
     * Obtener migraciones ejecutadas
     */
    private function getExecutedMigrations(): array
    {
        $sql = "SELECT migration FROM migrations ORDER BY id";
        $results = $this->connectionManager->select($sql);
        
        return array_column($results, 'migration');
    }

    /**
     * Ejecutar migración individual
     */
    private function executeMigration(string $file, string $migrationName): void
    {
        // Incluir y ejecutar la migración
        require_once $file;
        
        $className = $this->getMigrationClassName($file);
        $migration = new $className($this->connectionManager);
        
        // Ejecutar dentro de transacción
        $this->connectionManager->beginTransaction();
        
        try {
            $migration->up();
            
            // Registrar migración como ejecutada
            $this->recordMigration($migrationName);
            
            $this->connectionManager->commit();
            
        } catch (\Exception $e) {
            $this->connectionManager->rollback();
            throw ConnectionException::migrationFailed($migrationName, $e);
        }
    }

    /**
     * Registrar migración como ejecutada
     */
    private function recordMigration(string $migrationName): void
    {
        $batch = $this->getNextBatchNumber();
        $sql = "INSERT INTO migrations (migration, batch) VALUES (?, ?)";
        $this->connectionManager->execute($sql, [$migrationName, $batch]);
    }

    /**
     * Obtener siguiente número de batch
     */
    private function getNextBatchNumber(): int
    {
        $sql = "SELECT MAX(batch) as max_batch FROM migrations";
        $result = $this->connectionManager->fetch($sql);
        
        return ($result['max_batch'] ?? 0) + 1;
    }

    /**
     * Obtener nombre de clase de migración
     */
    private function getMigrationClassName(string $file): string
    {
        $content = file_get_contents($file);
        preg_match('/class\s+(\w+)\s+extends\s+Migration/', $content, $matches);
        
        if (!isset($matches[1])) {
            throw new \Exception("No se pudo encontrar la clase de migración en {$file}");
        }
        
        return $matches[1];
    }

    /**
     * Construir SQL para crear tabla
     */
    private function buildCreateTableSQL(string $tableName, array $columns, array $options): string
    {
        $columnDefinitions = [];
        
        foreach ($columns as $column) {
            $columnDefinitions[] = $this->buildColumnDefinition($column);
        }
        
        // Agregar primary key si se especifica
        if (isset($options['primary_key'])) {
            $columnDefinitions[] = "PRIMARY KEY ({$options['primary_key']})";
        }
        
        // Agregar índices si se especifican
        if (isset($options['indexes'])) {
            foreach ($options['indexes'] as $index) {
                $columnDefinitions[] = "KEY {$index['name']} ({$index['columns']})";
            }
        }
        
        $tableOptions = "ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
        
        return sprintf(
            "CREATE TABLE %s (\n    %s\n) %s",
            $tableName,
            implode(",\n    ", $columnDefinitions),
            $tableOptions
        );
    }

    /**
     * Construir definición de columna
     */
    private function buildColumnDefinition(array $column): string
    {
        $definition = "{$column['name']} {$column['type']}";
        
        if (isset($column['unsigned']) && $column['unsigned']) {
            $definition .= ' UNSIGNED';
        }
        
        if (isset($column['nullable']) && !$column['nullable']) {
            $definition .= ' NOT NULL';
        } else {
            $definition .= ' NULL';
        }
        
        if (isset($column['default'])) {
            $definition .= " DEFAULT '{$column['default']}'";
        }
        
        if (isset($column['auto_increment']) && $column['auto_increment']) {
            $definition .= ' AUTO_INCREMENT';
        }
        
        if (isset($column['comment'])) {
            $definition .= " COMMENT '{$column['comment']}'";
        }
        
        return $definition;
    }

    /**
     * Construir SQL para alterar tabla
     */
    private function buildAlterTableSQL(string $tableName, array $modifications): string
    {
        $modificationStatements = [];
        
        foreach ($modifications as $modification) {
            switch ($modification['type']) {
                case 'add_column':
                    $modificationStatements[] = "ADD COLUMN " . $this->buildColumnDefinition($modification['column']);
                    break;
                case 'drop_column':
                    $modificationStatements[] = "DROP COLUMN {$modification['name']}";
                    break;
                case 'modify_column':
                    $modificationStatements[] = "MODIFY COLUMN " . $this->buildColumnDefinition($modification['column']);
                    break;
            }
        }
        
        return sprintf(
            "ALTER TABLE %s %s",
            $tableName,
            implode(', ', $modificationStatements)
        );
    }

    /**
     * Verificar integridad del esquema
     */
    public function verifySchemaIntegrity(array $expectedTables): array
    {
        $issues = [];
        
        foreach ($expectedTables as $tableName => $schema) {
            if (!$this->tableExists($tableName)) {
                $issues[] = "Tabla faltante: {$tableName}";
                continue;
            }
            
            // Verificar columnas principales
            $columns = $this->getTableColumns($tableName);
            $columnNames = array_column($columns, 'name');
            
            foreach ($schema['columns'] as $expectedColumn) {
                if (!in_array($expectedColumn['name'], $columnNames)) {
                    $issues[] = "Columna faltante en {$tableName}: {$expectedColumn['name']}";
                }
            }
        }
        
        return $issues;
    }
}
