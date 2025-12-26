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
    /** @var ConnectionManager */
    private $connectionManager;
    /** @var SubjectPrerequisiteRepository */
    private $subjectPrerequisiteRepository;

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

            $period = $this->connectionManager->fetch("SELECT * FROM terms WHERE id = :id LIMIT 1", [
                'id' => $course['term_id']
            ]);

            if (!$period || $period['status'] !== 'active') {
                throw new DatabaseException('No hay un periodo académico activo para este curso.');
            }

            if (!empty($period['enrollment_start']) && !empty($period['enrollment_end'])) {
                $now = new \DateTimeImmutable();
                $start = new \DateTimeImmutable($period['enrollment_start']);
                $end = new \DateTimeImmutable($period['enrollment_end']);
                if ($now < $start || $now > $end) {
                    throw new DatabaseException('La ventana de inscripción está cerrada.');
                }
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

            if (!$overrideSchedule && !empty($course['schedule_label'])) {
                $conflict = $this->connectionManager->fetch(
                    "SELECT 1
                     FROM enrollments e
                     INNER JOIN courses c ON c.id = e.course_id
                     WHERE e.student_id = :student_id
                       AND c.term_id = :term_id
                       AND c.schedule_label = :schedule_label
                     LIMIT 1",
                    [
                        'student_id' => $studentId,
                        'term_id' => $course['term_id'],
                        'schedule_label' => $course['schedule_label']
                    ]
                );

                if ($conflict) {
                    throw new DatabaseException('El estudiante tiene un choque de horario en este periodo.');
                }
            }

            $this->connectionManager->execute(
                "INSERT INTO enrollments (student_id, course_id, enrollment_at, status, payment_status, total_amount, paid_amount, created_at)
                 VALUES (:student_id, :course_id, NOW(), 'active', 'pending', 0, 0, NOW())",
                [
                    'student_id' => $studentId,
                    'course_id' => $courseId
                ]
            );
        } catch (DatabaseException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DatabaseException('Error al crear inscripción: ' . $e->getMessage());
        }
    }
}
