<?php
/**
 * =============================================================================
 * REPOSITORIO CONCRETO: COURSE TEACHER REPOSITORY - INFRASTRUCTURE LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Repositories;

use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;

class CourseTeacherRepository
{
    /** @var ConnectionManager */
    private $connectionManager;
    /** @var string */
    private $tableName= 'course_teachers';

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    public function findCoursesByTeacher(int $teacherId): array
    {
        try {
            $sql = "
                SELECT c.*
                FROM {$this->tableName} ct
                INNER JOIN courses c ON c.id = ct.course_id
                WHERE ct.teacher_id = :teacher_id
            ";
            return $this->connectionManager->select($sql, ['teacher_id' => $teacherId]);
        } catch (\Exception $e) {
            throw new DatabaseException('Error al obtener cursos del profesor: ' . $e->getMessage());
        }
    }

    public function assignTeachers(int $courseId, array $teacherIds): void
    {
        try {
            $this->connectionManager->execute("DELETE FROM {$this->tableName} WHERE course_id = :course_id", [
                'course_id' => $courseId
            ]);

            foreach ($teacherIds as $teacherId) {
                $this->connectionManager->execute(
                    "INSERT INTO {$this->tableName} (course_id, teacher_id) VALUES (:course_id, :teacher_id)",
                    [
                        'course_id' => $courseId,
                        'teacher_id' => $teacherId
                    ]
                );
            }
        } catch (\Exception $e) {
            throw new DatabaseException('Error al asignar profesores: ' . $e->getMessage());
        }
    }

    public function hasScheduleConflict(int $teacherId, int $academicPeriodId, string $dayOfWeek, string $startTime, string $endTime, ?int $ignoreCourseId = null): bool
    {
        try {
            $sql = "
                SELECT 1
                FROM {$this->tableName} ct
                INNER JOIN courses c ON c.id = ct.course_id
                WHERE ct.teacher_id = :teacher_id
                  AND c.term_id = :term_id
                  AND c.schedule_label LIKE :day_of_week
                  AND c.schedule_label LIKE :start_time
                  AND c.schedule_label LIKE :end_time
            ";
            $params = [
                'teacher_id' => $teacherId,
                'term_id' => $academicPeriodId,
                'day_of_week' => '%' . $dayOfWeek . '%',
                'start_time' => '%' . $startTime . '%',
                'end_time' => '%' . $endTime . '%'
            ];

            if ($ignoreCourseId) {
                $sql .= " AND c.id != :ignore_course_id";
                $params['ignore_course_id'] = $ignoreCourseId;
            }

            $result = $this->connectionManager->fetch($sql, $params);
            return $result !== null;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al validar choque de horario del profesor: ' . $e->getMessage());
        }
    }
}
