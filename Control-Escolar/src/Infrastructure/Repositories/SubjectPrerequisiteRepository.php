<?php
/**
 * =============================================================================
 * REPOSITORIO CONCRETO: SUBJECT PREREQUISITE REPOSITORY - INFRASTRUCTURE LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Repositories;

use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;

class SubjectPrerequisiteRepository
{
    private ConnectionManager $connectionManager;
    private string $tableName = 'subject_prerequisites';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    public function findPrerequisites(int $subjectId): array
    {
        try {
            $sql = "SELECT prerequisite_id FROM {$this->tableName} WHERE subject_id = :subject_id";
            $results = $this->connectionManager->select($sql, ['subject_id' => $subjectId]);
            return array_column($results, 'prerequisite_id');
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener seriación: ' . $e->getMessage());
        }
    }

    public function findAvailableSubjectIdsForStudent(int $studentId): array
    {
        try {
            $completedSql = "
                SELECT DISTINCT c.subject_id
                FROM enrollments e
                INNER JOIN courses c ON c.id = e.course_id
                WHERE e.student_id = :student_id
                  AND e.status IN ('completed', 'passed', 'approved')
            ";
            $completed = $this->connectionManager->select($completedSql, ['student_id' => $studentId]);
            $completedIds = array_column($completed, 'subject_id');
            $completedList = $completedIds ? implode(',', array_map('intval', $completedIds)) : '0';

            $sql = "
                SELECT s.id
                FROM subjects s
                WHERE s.is_active = 1
                  AND NOT EXISTS (
                    SELECT 1
                    FROM {$this->tableName} sp
                    WHERE sp.subject_id = s.id
                      AND sp.prerequisite_id NOT IN ({$completedList})
                  )
                ORDER BY s.sort_order ASC
            ";
            $results = $this->connectionManager->select($sql);
            return array_column($results, 'id');
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener materias disponibles: ' . $e->getMessage());
        }
    }
}
