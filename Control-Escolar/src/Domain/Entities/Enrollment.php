<?php
/**
 * =============================================================================
 * ENTIDAD ENROLLMENT - DOMAIN LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\Entities;

use ChristianLMS\Domain\ValueObjects\{
    EnrollmentId,
    EnrollmentStatus,
    UserId,
    CourseId
};

/**
 * Entidad Enrollment
 *
 * Representa una inscripción de usuario en un curso.
 */
class Enrollment
{
    private EnrollmentId $id;
    private UserId $userId;
    private CourseId $courseId;
    private EnrollmentStatus $status;
    private ?UserId $enrolledBy;
    private bool $overrideSeriation = false;
    private bool $overrideSchedule = false;

    public function __construct(
        EnrollmentId $id,
        UserId $userId,
        CourseId $courseId
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->courseId = $courseId;
        $this->status = EnrollmentStatus::enrolled();
        $this->enrolledBy = null;
    }

    /**
     * Crear nueva inscripción
     */
    public static function create(string $userId, string $courseId): self
    {
        return new self(
            EnrollmentId::generate(),
            new UserId($userId),
            new CourseId($courseId)
        );
    }

    public function getId(): EnrollmentId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getCourseId(): CourseId
    {
        return $this->courseId;
    }

    public function getStatus(): EnrollmentStatus
    {
        return $this->status;
    }

    public function getEnrolledBy(): ?UserId
    {
        return $this->enrolledBy;
    }

    public function hasOverrideSeriation(): bool
    {
        return $this->overrideSeriation;
    }

    public function hasOverrideSchedule(): bool
    {
        return $this->overrideSchedule;
    }

    public function setStatus(EnrollmentStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setEnrolledBy(?UserId $enrolledBy): self
    {
        $this->enrolledBy = $enrolledBy;
        return $this;
    }

    public function setOverrideSeriation(bool $overrideSeriation): self
    {
        $this->overrideSeriation = $overrideSeriation;
        return $this;
    }

    public function setOverrideSchedule(bool $overrideSchedule): self
    {
        $this->overrideSchedule = $overrideSchedule;
        return $this;
    }

    /**
     * Convertir a array para transporte
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'user_id' => $this->userId->getValue(),
            'course_id' => $this->courseId->getValue(),
            'status' => $this->status->getValue(),
            'enrolled_by' => $this->enrolledBy?->getValue(),
            'override_seriation' => $this->overrideSeriation ? 1 : 0,
            'override_schedule' => $this->overrideSchedule ? 1 : 0
        ];
    }
}
