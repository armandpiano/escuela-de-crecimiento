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
    UserStatus,
    UserGender,
    PasswordHash
};
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;
use ChristianLMS\Infrastructure\Persistence\Schema\SchemaMap;

/**
 * Repositorio Concreto de Usuario
 * 
 * Implementación de la interfaz UserRepositoryInterface
 * Maneja la persistencia de usuarios en la base de datos
 */
class UserRepository implements UserRepositoryInterface
{
    /** @var ConnectionManager */
    private $connectionManager;
    /** @var string */
    private $tableName = 'users';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
        $this->tableName = SchemaMap::table('users');
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
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id LIMIT 1";
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
            $sql = "SELECT * FROM {$this->tableName} WHERE email = :email LIMIT 1";
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
            $sql = "SELECT * FROM {$this->tableName} WHERE LOWER(email) = LOWER(:email) LIMIT 1";
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
            $sql = "SELECT * FROM {$this->tableName} WHERE 1=1 ORDER BY created_at DESC";
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
            $sql = "SELECT * FROM {$this->tableName} WHERE status = :status ORDER BY created_at DESC";
            $params = ['status' => $this->mapStatusToDb($status)];
            
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
                    WHERE role = :role 
                    
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
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE role IN ({$placeholders})
                    ORDER BY created_at DESC";

            $results = $this->connectionManager->query($sql, array_values($roles));
            
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
                    SET status = 'blocked' 
                    WHERE id = :id";
            $params = [
                'id' => $id->getValue()
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id";
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE email = :email";
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE 1=1";
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
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE status = :status";
            $params = ['status' => $this->mapStatusToDb($status)];
            
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
                    WHERE role = :role 
                   ";
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
                    WHERE 1=1 
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
            $whereConditions = ['1=1'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['status'])) {
                $whereConditions[] = 'status = :status';
                $params['status'] = $this->mapStatusValueToDb($criteria['status']);
            }
            
            if (isset($criteria['role'])) {
                $whereConditions[] = 'role = :role';
                $params['role'] = $criteria['role'];
            }
            
            if (isset($criteria['name'])) {
                $whereConditions[] = 'name LIKE :name';
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
                    WHERE updated_at < DATE_SUB(NOW(), INTERVAL :days DAY)
                    ORDER BY updated_at ASC";
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
                    WHERE name LIKE :name 
                    ORDER BY name ASC";
            $params = [
                'name' => '%' . $name . '%'
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
            $year = (int) date('Y');
            $row = $this->connectionManager->query(
                "SELECT last_number FROM matricula_sequences WHERE year = :year LIMIT 1",
                ['year' => $year]
            );

            if (!$row) {
                $this->connectionManager->execute(
                    "INSERT INTO matricula_sequences (year, last_number) VALUES (:year, 0)",
                    ['year' => $year]
                );
                $lastNumber = 0;
            } else {
                $lastNumber = (int) $row['last_number'];
            }

            $nextNumber = $lastNumber + 1;
            $this->connectionManager->execute(
                "UPDATE matricula_sequences SET last_number = :last_number WHERE year = :year",
                ['last_number' => $nextNumber, 'year' => $year]
            );

            return $nextNumber;
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
                    WHERE email = :email AND id != :id";
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
                        SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked,
                        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                        SUM(CASE WHEN role = 'teacher' THEN 1 ELSE 0 END) as teachers,
                        SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as students
                    FROM {$this->tableName}";
            
            $result = $this->connectionManager->query($sql);
            return $result;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener estadísticas de usuarios: ' . $e->getMessage());
        }
    }

    // Métodos adicionales de la interfaz...

    public function findPendingActivation(): array
    {
        return $this->findByStatus(UserStatus::inactive());
    }

    public function findBlocked(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE status = 'blocked' ORDER BY created_at DESC";
            $results = $this->connectionManager->query($sql);

            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuarios bloqueados: ' . $e->getMessage());
        }
    }

    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        try {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            if (!SchemaMap::hasColumn($this->tableName, $orderBy)) {
                $orderBy = 'created_at';
            }

            $sql = "SELECT * FROM {$this->tableName} ORDER BY $orderBy $direction";
            $results = $this->connectionManager->query($sql);

            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al ordenar usuarios: ' . $e->getMessage());
        }
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
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE created_at BETWEEN :start_date AND :end_date
                    ORDER BY created_at DESC";
            $params = [
                'start_date' => $startDate,
                'end_date' => $endDate
            ];

            $results = $this->connectionManager->query($sql, $params);

            return array_map([$this, 'hydrateUser'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar usuarios por rango de fechas: ' . $e->getMessage());
        }
    }

    public function cleanExpiredTokens(): int
    {
        // TODO: Implementar limpieza de tokens expirados
        return 0;
    }

    public function findLastCreated(): ?User
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} ORDER BY created_at DESC LIMIT 1";
            $result = $this->connectionManager->query($sql);

            return $result ? $this->hydrateUser($result) : null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar el último usuario creado: ' . $e->getMessage());
        }
    }

    public function verifyIntegrity(): array
    {
        try {
            $issues = [];
            $sql = "SELECT email, COUNT(*) as count FROM {$this->tableName}
                    GROUP BY email HAVING count > 1";
            $results = $this->connectionManager->query($sql);
            if (!empty($results)) {
                $issues[] = 'Encontrados emails duplicados';
            }

            return $issues;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar integridad de usuarios: ' . $e->getMessage());
        }
    }

    /**
     * Insertar nuevo usuario
     */
    private function insert(User $user): void
    {
        $userArray = $this->buildPersistencePayload($user);
        $columns = array_keys($userArray);
        $placeholders = array_map(function ($col) {
            return ":$col";
        }, $columns);
        
        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->connectionManager->execute($sql, $userArray);
    }

    /**
     * Actualizar usuario existente
     */
    private function update(User $user): void
    {
        $userArray = $this->buildPersistencePayload($user);
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
        $nameParts = preg_split('/\\s+/', trim($data['name']), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $user = new User(
            UserId::fromString($data['id']),
            $firstName,
            $lastName,
            new Email($data['email']),
            $data['password_hash'] ? PasswordHash::fromHash($data['password_hash']) : null
        );

        $user->setStatus($this->mapStatusFromDb($data['status']));
        $user->setGender(UserGender::getDefault());
        $user->setPhone(null);
        $user->setAddress(null);
        $user->setProfilePhoto(null);
        $user->setRoles([$data['role']]);
        $user->setMetadata([]);
        $user->setLastLoginAt(null);
        $user->setLoginAttempts(0);
        $user->setLockedUntil(null);

        return $user;
    }

    private function buildPersistencePayload(User $user): array
    {
        $data = [
            'id' => $user->getId()->getValue(),
            'name' => $user->getFullName(),
            'email' => $user->getEmailString(),
            'password_hash' => $user->getPasswordHash() ? $user->getPasswordHash()->getValue() : null,
            'google_id' => null,
            'role' => $this->resolvePrimaryRole($user->getRoles()),
            'status' => $this->mapStatusToDb($user->getStatus()),
            'created_at' => $user->getCreatedAt(),
            'updated_at' => $user->getUpdatedAt(),
        ];

        $allowed = array_flip(SchemaMap::columns($this->tableName));

        return array_intersect_key($data, $allowed);
    }

    private function resolvePrimaryRole(array $roles): string
    {
        return $roles ? $roles[0] : 'student';
    }

    private function mapStatusToDb(UserStatus $status): string
    {
        switch ($status->getValue()) {
            case UserStatus::ACTIVE:
                return 'active';
            case UserStatus::INACTIVE:
                return 'inactive';
            case UserStatus::SUSPENDED:
            case UserStatus::BANNED:
            case UserStatus::DELETED:
                return 'blocked';
            case UserStatus::PENDING:
            default:
                return 'inactive';
        }
    }

    private function mapStatusFromDb(string $status): UserStatus
    {
        switch ($status) {
            case 'active':
                return UserStatus::active();
            case 'blocked':
                return UserStatus::suspended();
            case 'inactive':
            default:
                return UserStatus::inactive();
        }
    }

    private function mapStatusValueToDb(string $status): string
    {
        switch ($status) {
            case 'active':
            case 'inactive':
            case 'blocked':
                return $status;
            case 'suspended':
            case 'banned':
            case 'deleted':
                return 'blocked';
            default:
                return 'inactive';
        }
    }
}
