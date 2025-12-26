<?php
/**
 * =============================================================================
 * REPOSITORIO CONCRETO: SUBJECT REPOSITORY - INFRASTRUCTURE LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Repositories;

use ChristianLMS\Domain\Entities\Subject;
use ChristianLMS\Domain\Ports\SubjectRepositoryInterface;
use ChristianLMS\Domain\ValueObjects\{
    SubjectId,
    SubjectCode,
    SubjectStatus,
    GradeLevel
};
use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;
use ChristianLMS\Infrastructure\Persistence\Schema\SchemaMap;

/**
 * Repositorio Concreto de Materia
 * 
 * Implementación de la interfaz SubjectRepositoryInterface
 * Maneja la persistencia de materias en la base de datos
 */
class SubjectRepository implements SubjectRepositoryInterface
{
    /** @var ConnectionManager */
    private $connectionManager;
    /** @var string */
    private $tableName = 'subjects';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
        $this->tableName = SchemaMap::table('subjects');
    }

    /**
     * Guardar materia (crear o actualizar)
     */
    public function save(Subject $subject): Subject
    {
        try {
            $subjectArray = $subject->toArray();
            $subjectArray['updated_at'] = date('Y-m-d H:i:s');
            
            if ($this->existsById($subject->getId())) {
                // Actualizar materia existente
                $this->update($subject);
            } else {
                // Crear nueva materia
                $subjectArray['created_at'] = date('Y-m-d H:i:s');
                $this->insert($subject);
            }
            
            return $subject;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al guardar materia: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materia por ID
     */
    public function findById(SubjectId $id): ?Subject
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE id = :id LIMIT 1";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateSubject($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materia por ID: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materia por código
     */
    public function findByCode(SubjectCode $code): ?Subject
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} WHERE code = :code LIMIT 1";
            $params = ['code' => $code->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            
            if ($result) {
                return $this->hydrateSubject($result);
            }
            
            return null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materia por código: ' . $e->getMessage());
        }
    }

    /**
     * Buscar todas las materias
     */
    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} ORDER BY name ASC";
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar todas las materias: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias por departamento
     */
    public function findByDepartment(string $department): array
    {
        if (!SchemaMap::hasColumn($this->tableName, 'department')) {
            return $this->findAll();
        }

        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE department = :department 
                    ORDER BY name ASC";
            $params = ['department' => $department];

            $results = $this->connectionManager->query($sql, $params);

            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias por departamento: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias por nivel educativo
     */
    public function findByGradeLevel(GradeLevel $gradeLevel): array
    {
        if (!SchemaMap::hasColumn($this->tableName, 'grade_level')) {
            return $this->findAll();
        }

        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE grade_level = :grade_level 
                    ORDER BY name ASC";
            $params = ['grade_level' => $gradeLevel->getValue()];

            $results = $this->connectionManager->query($sql, $params);

            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias por nivel educativo: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias por estado
     */
    public function findByStatus(SubjectStatus $status): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE is_active = :is_active 
                    ORDER BY name ASC";
            $params = ['is_active' => $status->isActive() ? 1 : 0];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias por estado: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias activas
     */
    public function findActive(): array
    {
        return $this->findByStatus(\ChristianLMS\Domain\ValueObjects\SubjectStatus::active());
    }

    /**
     * Buscar materias básicas
     */
    public function findCore(): array
    {
        return $this->findActive();
    }

    /**
     * Buscar materias electivas
     */
    public function findElective(): array
    {
        return $this->findActive();
    }

    /**
     * Eliminar materia
     */
    public function delete(SubjectId $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->tableName} WHERE id = :id";
            $params = ['id' => $id->getValue()];
            
            return $this->connectionManager->execute($sql, $params) > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar materia: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar materia de forma suave (soft delete)
     */
    public function softDelete(SubjectId $id): bool
    {
        try {
            $sql = "UPDATE {$this->tableName} 
                    SET is_active = 0 
                    WHERE id = :id";
            $params = [
                'id' => $id->getValue()
            ];
            
            return $this->connectionManager->execute($sql, $params) > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al eliminar materia suavemente: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe una materia con el ID dado
     */
    public function existsById(SubjectId $id): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE id = :id";
            $params = ['id' => $id->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de materia por ID: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si existe una materia con el código dado
     */
    public function existsByCode(SubjectCode $code): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE code = :code";
            $params = ['code' => $code->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return $result['count'] > 0;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar existencia de materia por código: ' . $e->getMessage());
        }
    }

    /**
     * Contar total de materias
     */
    public function count(): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName}";
            $result = $this->connectionManager->query($sql);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar materias: ' . $e->getMessage());
        }
    }

    /**
     * Contar materias por estado
     */
    public function countByStatus(SubjectStatus $status): int
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE is_active = :is_active";
            $params = ['is_active' => $status->isActive() ? 1 : 0];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar materias por estado: ' . $e->getMessage());
        }
    }

    /**
     * Contar materias por departamento
     */
    public function countByDepartment(string $department): int
    {
        if (!SchemaMap::hasColumn($this->tableName, 'department')) {
            return $this->count();
        }

        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE department = :department";
            $params = ['department' => $department];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar materias por departamento: ' . $e->getMessage());
        }
    }

    /**
     * Contar materias por nivel educativo
     */
    public function countByGradeLevel(GradeLevel $gradeLevel): int
    {
        if (!SchemaMap::hasColumn($this->tableName, 'grade_level')) {
            return $this->count();
        }

        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                    WHERE grade_level = :grade_level";
            $params = ['grade_level' => $gradeLevel->getValue()];
            
            $result = $this->connectionManager->query($sql, $params);
            return (int) $result['count'];
        } catch (\Exception $e) {
            throw new DatabaseException('Error al contar materias por nivel educativo: ' . $e->getMessage());
        }
    }

    /**
     * Obtener materias paginadas
     */
    public function findPaginated(int $page = 1, int $perPage = 20): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    ORDER BY name ASC 
                    LIMIT :limit OFFSET :offset";
            
            $params = [
                'limit' => $perPage,
                'offset' => $offset
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener materias paginadas: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias con filtros
     */
    public function search(array $criteria, int $page = 1, int $perPage = 20): array
    {
        try {
            $whereConditions = ['1=1'];
            $params = [];
            
            // Aplicar criterios de búsqueda
            if (isset($criteria['status'])) {
                $whereConditions[] = 'is_active = :is_active';
                $params['is_active'] = $criteria['status'] === 'active' ? 1 : 0;
            }
            
            if (isset($criteria['name'])) {
                $whereConditions[] = 'name LIKE :name';
                $params['name'] = '%' . $criteria['name'] . '%';
            }
            
            $offset = ($page - 1) * $perPage;
            $params['limit'] = $perPage;
            $params['offset'] = $offset;
            
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE " . implode(' AND ', $whereConditions) . "
                    ORDER BY name ASC 
                    LIMIT :limit OFFSET :offset";
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias: ' . $e->getMessage());
        }
    }

    /**
     * Obtener materias recientes
     */
    public function findRecent(int $days = 30): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY) 
                    ORDER BY created_at DESC";
            $params = ['days' => $days];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias recientes: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias por nombre (búsqueda parcial)
     */
    public function findByName(string $name): array
    {
        try {
            $sql = "SELECT * FROM {$this->tableName} 
                    WHERE (name LIKE :name OR code LIKE :code) 
                    ORDER BY name ASC";
            $params = [
                'name' => '%' . $name . '%',
                'code' => '%' . $name . '%'
            ];
            
            $results = $this->connectionManager->query($sql, $params);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias por nombre: ' . $e->getMessage());
        }
    }

    /**
     * Obtener materias ordenadas
     */
    public function findOrdered(string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        try {
            $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
            if (!SchemaMap::hasColumn($this->tableName, $orderBy)) {
                $orderBy = 'created_at';
            }
            
            $sql = "SELECT * FROM {$this->tableName} 
                    ORDER BY $orderBy $direction";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener materias ordenadas: ' . $e->getMessage());
        }
    }

    /**
     * Obtener todos los departamentos únicos
     */
    public function findDepartments(): array
    {
        if (!SchemaMap::hasColumn($this->tableName, 'department')) {
            return [];
        }

        try {
            $sql = "SELECT DISTINCT department FROM {$this->tableName} 
                    WHERE department IS NOT NULL AND department != '' 
                    ORDER BY department ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_column($results, 'department');
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener departamentos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener todos los niveles educativos únicos
     */
    public function findGradeLevels(): array
    {
        if (!SchemaMap::hasColumn($this->tableName, 'grade_level')) {
            return [];
        }

        try {
            $sql = "SELECT DISTINCT grade_level FROM {$this->tableName} 
                    WHERE grade_level IS NOT NULL AND grade_level != '' 
                    ORDER BY grade_level ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_column($results, 'grade_level');
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener niveles educativos: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de materias
     */
    public function getStatistics(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
                    FROM {$this->tableName}";
            
            $result = $this->connectionManager->query($sql);
            return $result;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener estadísticas de materias: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias con prerrequisitos
     */
    public function findWithPrerequisites(): array
    {
        try {
            $sql = "SELECT DISTINCT s.* FROM {$this->tableName} s
                    INNER JOIN subject_prerequisites sp ON sp.subject_id = s.id
                    ORDER BY s.name ASC";
            
            $results = $this->connectionManager->query($sql);
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias con prerrequisitos: ' . $e->getMessage());
        }
    }

    /**
     * Buscar materias disponibles para cursos
     */
    public function findAvailableForCourses(): array
    {
        return $this->findByStatus(\ChristianLMS\Domain\ValueObjects\SubjectStatus::active());
    }

    /**
     * Obtener materias por rango de fechas
     */
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
            
            return array_map([$this, 'hydrateSubject'], $results);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al buscar materias por rango de fechas: ' . $e->getMessage());
        }
    }

    /**
     * Verificar integridad de datos
     */
    public function verifyIntegrity(): array
    {
        try {
            $issues = [];
            
            // Verificar códigos duplicados
            $sql = "SELECT code, COUNT(*) as count FROM {$this->tableName} 
                    GROUP BY code HAVING count > 1";
            $results = $this->connectionManager->query($sql);
            if (!empty($results)) {
                $issues[] = 'Encontrados códigos de materia duplicados';
            }
            
            return $issues;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al verificar integridad de materias: ' . $e->getMessage());
        }
    }

    /**
     * Insertar nueva materia
     */
    private function insert(Subject $subject): void
    {
        $subjectArray = $this->buildPersistencePayload($subject);
        $columns = array_keys($subjectArray);
        $placeholders = array_map(function ($col) {
            return ":$col";
        }, $columns);
        
        $sql = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->connectionManager->execute($sql, $subjectArray);
    }

    /**
     * Actualizar materia existente
     */
    private function update(Subject $subject): void
    {
        $subjectArray = $this->buildPersistencePayload($subject);
        $updateFields = [];
        
        foreach ($subjectArray as $column => $value) {
            if ($column !== 'id' && $column !== 'created_at') {
                $updateFields[] = "$column = :$column";
            }
        }
        
        $sql = "UPDATE {$this->tableName} 
                SET " . implode(', ', $updateFields) . "
                WHERE id = :id";
        
        $this->connectionManager->execute($sql, $subjectArray);
    }

    /**
     * Hidratar materia desde array de base de datos
     */
    private function hydrateSubject(array $data): Subject
    {
        $subject = new Subject(
            new SubjectId($data['id']),
            $data['name'],
            new SubjectCode($data['code'])
        );

        $subject->setDescription($data['description']);
        $subject->setDepartment(null);
        $subject->setGradeLevel(null);
        $subject->setIsCore(false);
        $subject->setCredits(0);
        $subject->setHoursPerWeek(0);
        $subject->setPrerequisites(null);
        $subject->setLearningOutcomes(null);
        $subject->setBibliography(null);
        $subject->setResources(null);
        $subject->setStatus($data['is_active'] ? \ChristianLMS\Domain\ValueObjects\SubjectStatus::active() : \ChristianLMS\Domain\ValueObjects\SubjectStatus::inactive());
        $subject->setMetadata([]);

        return $subject;
    }

    private function buildPersistencePayload(Subject $subject): array
    {
        $data = [
            'id' => $subject->getId()->getValue(),
            'code' => $subject->getCode()->getValue(),
            'name' => $subject->getName(),
            'module_id' => null,
            'module' => null,
            'description' => $subject->getDescription(),
            'is_active' => $subject->getStatus()->isActive() ? 1 : 0,
            'created_at' => $subject->getCreatedAt(),
            'updated_at' => $subject->getUpdatedAt(),
        ];

        $allowed = array_flip(SchemaMap::columns($this->tableName));

        return array_intersect_key($data, $allowed);
    }
}
