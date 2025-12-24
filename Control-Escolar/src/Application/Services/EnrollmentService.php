<?php
/**
 * =============================================================================
 * SERVICIO: ENROLLMENT SERVICE - APPLICATION LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Application\Services;

use ChristianLMS\Infrastructure\Persistence\Database\ConnectionManager;
use ChristianLMS\Infrastructure\Repositories\SubjectPrerequisiteRepository;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;

class EnrollmentService
{
    private ConnectionManager $connectionManager;
    private SubjectPrerequisiteRepository $subjectPrerequisiteRepository;

    public function __construct(ConnectionManager $connectionManager, SubjectPrerequisiteRepository $subjectPrerequisiteRepository)
    {
        $this->connectionManager = $connectionManager;
        $this->subjectPrerequisiteRepository = $subjectPrerequisiteRepository;
    }

    public function createEnrollment(int $studentId, int $courseId, ?int $enrolledBy = null, bool $overrideSeriation = false, bool $overrideSchedule = false): void
    {
        try {
            $course = $this->connectionManager->fetch("SELECT * FROM courses WHERE id = :course_id LIMIT 1", [
                'course_id' => $courseId
            ]);

            if (!$course) {
                throw new DatabaseException('Curso no encontrado.');
            }

            $period = $this->connectionManager->fetch("SELECT * FROM academic_periods WHERE id = :id LIMIT 1", [
                'id' => $course['academic_period_id']
            ]);

            if (!$period || $period['status'] !== 'active') {
                throw new DatabaseException('No hay un periodo académico activo para este curso.');
            }

            $now = new \DateTimeImmutable();
            $start = new \DateTimeImmutable($period['enrollment_start_date']);
            $end = new \DateTimeImmutable($period['enrollment_end_date']);
            if ($now < $start || $now > $end) {
                throw new DatabaseException('La ventana de inscripción está cerrada.');
            }

            $exists = $this->connectionManager->fetch(
                "SELECT 1 FROM enrollments WHERE student_id = :student_id AND course_id = :course_id LIMIT 1",
                [
                    'student_id' => $studentId,
                    'course_id' => $courseId
                ]
            );
            if ($exists) {
                throw new DatabaseException('El estudiante ya está inscrito en este curso.');
            }

            if (!$overrideSeriation) {
                $availableSubjects = $this->subjectPrerequisiteRepository->findAvailableSubjectIdsForStudent($studentId);
                if (!in_array($course['subject_id'], $availableSubjects, true)) {
                    throw new DatabaseException('El estudiante no cumple con la seriación requerida.');
                }
            }

            if (!$overrideSchedule) {
                $conflict = $this->connectionManager->fetch(
                    "SELECT 1
                     FROM enrollments e
                     INNER JOIN courses c ON c.id = e.course_id
                     WHERE e.student_id = :student_id
                       AND c.academic_period_id = :period_id
                       AND c.day_of_week = :day_of_week
                       AND c.start_time < :end_time
                       AND c.end_time > :start_time
                     LIMIT 1",
                    [
                        'student_id' => $studentId,
                        'period_id' => $course['academic_period_id'],
                        'day_of_week' => $course['day_of_week'],
                        'start_time' => $course['start_time'],
                        'end_time' => $course['end_time']
                    ]
                );

                if ($conflict) {
                    throw new DatabaseException('El estudiante tiene un choque de horario en este periodo.');
                }
            }

            $this->connectionManager->execute(
                "INSERT INTO enrollments (student_id, course_id, academic_period_id, status, enrolled_by, override_seriation, override_schedule, created_at)
                 VALUES (:student_id, :course_id, :academic_period_id, 'active', :enrolled_by, :override_seriation, :override_schedule, NOW())",
                [
                    'student_id' => $studentId,
                    'course_id' => $courseId,
                    'academic_period_id' => $course['academic_period_id'],
                    'enrolled_by' => $enrolledBy,
                    'override_seriation' => $overrideSeriation ? 1 : 0,
                    'override_schedule' => $overrideSchedule ? 1 : 0
                ]
            );
        } catch (DatabaseException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al crear inscripción: ' . $e->getMessage());
        }
    }
}
