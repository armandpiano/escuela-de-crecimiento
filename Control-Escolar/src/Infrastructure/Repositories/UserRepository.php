<?php
/**
 * =============================================================================
 * REPOSITORIO CONCRETO: USER REPOSITORY - INFRASTRUCTURE LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Repositories;

use ChristianLMS\Domain\Entities\User;
use ChristianLMS\Domain\Ports\UserRepositoryInterface;
use ChristianLMS\Domain\ValueObjects\{
    UserId,
    Email,
    UserStatus
};
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;
use Ramsey\Uuid\Uuid;

/**
 * Repositorio Concreto de Usuario
 * 
 * Implementación de la interfaz UserRepositoryInterface
 * Maneja la persistencia de usuarios en la base de datos
 */
class UserRepository implements UserRepositoryInterface
{
    private ConnectionManager $connectionManager;
    private string $tableName = 'users';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * Guardar usuario (crear o actualizar)
     */
    public function save(User $user): User
    {
        try {
            $userArray = $user->toArray();
            $userArray['updated_at'] = date('Y-m-d H:i:s');
            
            if ($this->existsById($user->getId())) {
                // Actualizar usuario existente
                $this->update($user);
            } else {
                // Crear nuevo usuario
                $userArray['created_at'] = date('Y-m-d H:i:s');
                $this->insert($user);
            }
            
            return $user;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al guardar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Buscar usuario por ID
     */
    public function findById(UserId $id): ?User
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id AND deleted_at IS NULL LIMIT 1";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateUser($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuario por ID: ' . $e->getMessage());
        }
    }

    /**
     * Buscar usuario por email
     */
    public function findByEmail(Email $email): ?User
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE email = :email AND deleted_at IS NULL LIMIT 1";
            $params = ['email' => $email->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateUser($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuario por email: ' . $e->getMessage());
        }
    }

    /**
     * Buscar usuario por email de forma case-insensitive
     */
    public function findByEmailCaseInsensitive(Email $email): ?User
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE LOWER(email) = LOWER(:email) AND deleted_at IS NULL LIMIT 1";
            $params = ['email' => $email->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateUser($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuario por email (case-insensitive): ' . $e->getMessage());
        }
    }

    /**
     * Buscar todos los usuarios
     */
    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE deleted_at IS NULL ORDER BY created_at DESC";
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar todos los usuarios: ' . $e->getMessage());
        }
    }

    /**
     * Buscar usuarios por estado
     */
    public function findByStatus(UserStatus $status): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE status = :status AND deleted_at IS NULL ORDER BY created_at DESC";
            $params = ['status' => $status->getValue()];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuarios por estado: ' . $e->getMessage());
        }
    }

    /**
     * Buscar usuarios activos
     */
    public function findActive(): array
    {
        return $this->findByStatus(\ChristianLMS\Domain\ValueObjects\UserStatus::active());
    }

    /**
     * Buscar usuarios inactivos
     */
    public function findInactive(): array
    {
        return $this->findByStatus(\ChristianLMS\Domain\ValueObjects\UserStatus::inactive());
    }

    /**
     * Buscar usuarios por rol
     */
    public function findByRole(string $role): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE JSON_CONTAINS(roles, JSON_QUOTE(:role)) 
                    AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            $params = ['role' => $role];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuarios por rol: ' . $e->getMessage());
        }
    }

    /**
     * Buscar usuarios con múltiples roles
     */
    public function findByRoles(array $roles): array
    {
        try {
            $roleConditions = [];
            $params = [];
            
            foreach ($roles as $index => $role) {
                $paramKey = "role_$index";
                $roleConditions[] = "JSON_CONTAINS(roles, JSON_QUOTE(:$paramKey))";
                $params[$paramKey] = $role;
            }
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE (" . implode(' OR ', $roleConditions) . ") 
                    AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuarios por múltiples roles: ' . $e->getMessage());
        }
    }

    /**
     * Buscar administradores
     */
    public function findAdmins(): array
    {
        return $this->findByRole('admin');
    }

    /**
     * Buscar profesores
     */
    public function findTeachers(): array
    {
        $teacherRoles = ['teacher', 'professor'];
        return $this->findByRoles($teacherRoles);
    }

    /**
     * Buscar estudiantes
     */
    public function findStudents(): array
    {
        $studentRoles = ['student', 'alumno'];
        return $this->findByRoles($studentRoles);
    }

    /**
     * Eliminar usuario
     */
    public function delete(UserId $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            $params = ['id' => $id->getValue()];
            
            return $this->connectionManager->execute($sql, $params) > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar usuario de forma suave (soft delete)
     */
    public function softDelete(UserId $id): bool
    {
        try {
            $sql = "UPDATE {$this->tableName} 
                    SET deleted_at = :deleted_at 
                    WHERE id = :id AND deleted_at IS NULL";
            $params = [
                'id' => $id->getValue(),
                'deleted_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->connectionManager->execute($sql, $params) > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar usuario suavemente: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe un usuario con el ID dado
     */
    public function existsById(UserId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id AND deleted_at IS NULL";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de usuario por ID: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe un usuario con el email dado
     */
    public function existsByEmail(Email $email): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE email = :email AND deleted_at IS NULL";
            $params = ['email' => $email->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de usuario por email: ' . $e->getMessage());
        }
    }

    /**
     * Contar total de usuarios
     */
    public function count(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE deleted_at IS NULL";
            $result = $this->connectionManager->query($sql);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar usuarios: ' . $e->getMessage());
        }
    }

    /**
     * Contar usuarios por estado
     */
    public function countByStatus(UserStatus $status): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = :status AND deleted_at IS NULL";
            $params = ['status' => $status->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar usuarios por estado: ' . $e->getMessage());
        }
    }

    /**
     * Contar usuarios por rol
     */
    public function countByRole(string $role): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE JSON_CONTAINS(roles, JSON_QUOTE(:role)) 
                    AND deleted_at IS NULL";
            $params = ['role' => $role];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar usuarios por rol: ' . $e->getMessage());
        }
    }

    /**
     * Obtener usuarios paginados
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE deleted_at IS NULL 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $params = [
                'limit' => $perPage,
                'offset' => $offset
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener usuarios paginados: ' . $e->getMessage());
        }
    }

    /**
     * Buscar usuarios con filtros
     */
    public function search(array $criteria, int $page = 1, int $perPage = 20): array
    {
        try {
            $whereConditions = ['deleted_at IS NULL'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['status'])) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $criteria['status'];
            }
            
            if (isset($criteria['role'])) {
                $whereConditions[] = 'JSON_CONTAINS(roles, JSON_QUOTE(:role))';
                $params['role'] = $criteria['role'];
            }
            
            if (isset($criteria['name'])) {
                $whereConditions[] = '(first_name LIKE :name OR last_name LIKE :name)';
                $params['name'] = '%' . $criteria['name'] . '%';
            }
            
            if (isset($criteria['email'])) {
                $whereConditions[] = 'email LIKE :email';
                $params['email'] = '%' . $criteria['email'] . '%';
            }
            
            $offset = ($page - 1) * $perPage;
            $params['limit'] = $perPage;
            $params['offset'] = $offset;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE " . implode(' AND ', $whereConditions) . "
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuarios: ' . $e->getMessage());
        }
    }

    /**
     * Obtener usuarios recientes (por fecha de creación)
     */
    public function findRecent(int $days = 30): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY) 
                    AND deleted_at IS NULL 
                    ORDER BY created_at DESC";
            $params = ['days' => $days];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuarios recientes: ' . $e->getMessage());
        }
    }

    /**
     * Obtener usuarios que no han iniciado sesión recientemente
     */
    public function findInactiveUsers(int $days = 90): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE (last_login_at IS NULL OR last_login_at < DATE_SUB(NOW(), INTERVAL :days DAY))
                    AND deleted_at IS NULL 
                    ORDER BY last_login_at ASC";
            $params = ['days' => $days];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuarios inactivos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar usuarios por nombre (búsqueda parcial)
     */
    public function findByName(string $name): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE (first_name LIKE :name OR last_name LIKE :name OR CONCAT(first_name, ' ', last_name) LIKE :fullname) 
                    AND deleted_at IS NULL 
                    ORDER BY first_name ASC, last_name ASC";
            $params = [
                'name' => '%' . $name . '%',
                'fullname' => '%' . $name . '%'
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuarios por nombre: ' . $e->getMessage());
        }
    }

    /**
     * Obtener el siguiente número de matrícula/secuencia
     */
    public function getNextMatriculaNumber(): int
    {
        try {
            $sql = "SELECT MAX(CAST(SUBSTRING(metadata->'$.matricula_number', 2) AS UNSIGNED)) as max_number 
                    FROM {$this->tableName} 
                    WHERE metadata->'$.matricula_number IS NOT NULL 
                    AND deleted_at IS NULL";
            
            $result = $this->connectionManager->query($sql);
            return ($result['max_number'] ?? 0) + 1;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener siguiente número de matrícula: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si un email ya existe (excluyendo un ID específico)
     */
    public function emailExistsExcluding(Email $email, UserId $excludeId): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE email = :email AND id != :id AND deleted_at IS NULL";
            $params = [
                'email' => $email->getValue(),
                'id' => $excludeId->getValue()
            ];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar email exclusivo: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de usuarios
     */
    public function getStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                        SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended,
                        SUM(CASE WHEN JSON_CONTAINS(roles, JSON_QUOTE('admin')) THEN 1 ELSE 0 END) as admins,
                        SUM(CASE WHEN JSON_CONTAINS(roles, JSON_QUOTE('teacher')) OR JSON_CONTAINS(roles, JSON_QUOTE('professor')) THEN 1 ELSE 0 END) as teachers,
                        SUM(CASE WHEN JSON_CONTAINS(roles, JSON_QUOTE('student')) OR JSON_CONTAINS(roles, JSON_QUOTE('alumno')) THEN 1 ELSE 0 END) as students,
                        SUM(CASE WHEN last_login_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as recently_active
                    FROM {$this->tableName} 
                    WHERE deleted_at IS NULL";
            
            $result = $this->connectionManager->query($sql);
            return $result;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener estadísticas de usuarios: ' . $e->getMessage());
        }
    }

    // Métodos adicionales de la interfaz...

    public function findPendingActivation(): array
    {
        // TODO: Implementar lógica de activación pendiente
        return [];
    }

    public function findBlocked(): array
    {
        // TODO: Implementar búsqueda de usuarios bloqueados
        return [];
    }

    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        // TODO: Implementar ordenamiento personalizado
        return [];
    }

    public function findByVerificationToken(string $token): ?User
    {
        // TODO: Implementar búsqueda por token de verificación
        return null;
    }

    public function findByPasswordResetToken(string $token): ?User
    {
        // TODO: Implementar búsqueda por token de reset de contraseña
        return null;
    }

    public function findByDateRange(string $startDate, string $endDate): array
    {
        // TODO: Implementar búsqueda por rango de fechas
        return [];
    }

    public function cleanExpiredTokens(): int
    {
        // TODO: Implementar limpieza de tokens expirados
        return 0;
    }

    public function findLastCreated(): ?User
    {
        // TODO: Implementar búsqueda del último usuario creado
        return null;
    }

    public function verifyIntegrity(): array
    {
        // TODO: Implementar verificación de integridad de datos
        return [];
    }

    /**
     * Insertar nuevo usuario
     */
    private function insert(User $user): void
    {
        $userArray = $user->toArray();
        $columns = array_keys($userArray);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->connectionManager->execute($sql, $userArray);
    }

    /**
     * Actualizar usuario existente
     */
    private function update(User $user): void
    {
        $userArray = $user->toArray();
        $updateFields = [];
        
        foreach ($userArray as $column => $value) {
            if ($column !== 'id' && $column !== 'created_at') {
                $updateFields[] = "$column = :$column";
            }
        }
        
        $sql = "UPDATE {$this->tableName} 
                SET " . implode(', ', $updateFields) . "
                WHERE id = :id";
        
        $this->connectionManager->execute($sql, $userArray);
    }

    /**
     * Hidratar usuario desde array de base de datos
     */
    private function hydrateUser(array $data): User
    {
        $user = new User(
            new UserId($data['id']),
            $data['first_name'],
            $data['last_name'],
            new \ChristianLMS\Domain\ValueObjects\Email($data['email']),
            $data['password_hash'] ? new \ChristianLMS\Domain\ValueObjects\PasswordHash($data['password_hash']) : null
        );

        $user->setStatus(new \ChristianLMS\Domain\ValueObjects\UserStatus($data['status']));
        $user->setGender(new \ChristianLMS\Domain\ValueObjects\UserGender($data['gender']));
        $user->setPhone($data['phone']);
        $user->setAddress($data['address']);
        $user->setProfilePhoto($data['profile_photo']);
        $user->setRoles(json_decode($data['roles'], true) ?? []);
        $user->setMetadata(json_decode($data['metadata'], true) ?? []);
        $user->setLastLoginAt($data['last_login_at']);
        $user->setLoginAttempts($data['login_attempts'] ?? 0);
        $user->setLockedUntil($data['locked_until']);

        return $user;
    }
}
