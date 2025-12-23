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
    PaymentStatus,
    UserId,
    CourseId,
    AcademicPeriodId
};
use ChristianLMS\Domain\Entities\Traits\Timestampable;
use ChristianLMS\Domain\Entities\Traits\SoftDeleteable;

/**
 * Entidad Enrollment
 * 
 * Representa una inscripción de estudiante en un curso
 * Cumple con los principios de DDD y arquitectura hexagonal.
 */
class Enrollment
{
    use Timestampable, SoftDeleteable;

    private EnrollmentId $id;
    private UserId $studentId;
    private CourseId $courseId;
    private AcademicPeriodId $academicPeriodId;
    private string $enrollmentDate;
    private EnrollmentStatus $status;
    private ?float $finalGrade;
    private ?string $letterGrade;
    private float $creditsEarned;
    private float $attendancePercentage;
    private PaymentStatus $paymentStatus;
    private float $paymentAmount;
    private ?string $paymentDate;
    private ?string $dropDate;
    private ?string $completionDate;
    private ?string $notes;
    private array $metadata = [];

    /**
     * Constructor
     */
    public function __construct(
        EnrollmentId $id,
        UserId $studentId,
        CourseId $courseId,
        AcademicPeriodId $academicPeriodId
    ) {
        $this->id = $id;
        $this->studentId = $studentId;
        $this->courseId = $courseId;
        $this->academicPeriodId = $academicPeriodId;
        $this->enrollmentDate = date('Y-m-d H:i:s');
        $this->status = EnrollmentStatus::enrolled();
        $this->creditsEarned = 0.0;
        $this->attendancePercentage = 0.0;
        $this->paymentStatus = PaymentStatus::pending();
        $this->paymentAmount = 0.0;
    }

    /**
     * Crear nueva inscripción
     */
    public static function create(
        string $studentId,
        string $courseId,
        string $academicPeriodId
    ): self {
        $enrollment = new self(
            EnrollmentId::generate(),
            new UserId($studentId),
            new CourseId($courseId),
            new AcademicPeriodId($academicPeriodId)
        );
        
        return $enrollment;
    }

    // Getters

    public function getId(): EnrollmentId
    {
        return $this->id;
    }

    public function getStudentId(): UserId
    {
        return $this->studentId;
    }

    public function getCourseId(): CourseId
    {
        return $this->courseId;
    }

    public function getAcademicPeriodId(): AcademicPeriodId
    {
        return $this->academicPeriodId;
    }

    public function getEnrollmentDate(): string
    {
        return $this->enrollmentDate;
    }

    public function getStatus(): EnrollmentStatus
    {
        return $this->status;
    }

    public function getFinalGrade(): ?float
    {
        return $this->finalGrade;
    }

    public function getLetterGrade(): ?string
    {
        return $this->letterGrade;
    }

    public function getCreditsEarned(): float
    {
        return $this->creditsEarned;
    }

    public function getAttendancePercentage(): float
    {
        return $this->attendancePercentage;
    }

    public function getPaymentStatus(): PaymentStatus
    {
        return $this->paymentStatus;
    }

    public function getPaymentAmount(): float
    {
        return $this->paymentAmount;
    }

    public function getPaymentDate(): ?string
    {
        return $this->paymentDate;
    }

    public function getDropDate(): ?string
    {
        return $this->dropDate;
    }

    public function getCompletionDate(): ?string
    {
        return $this->completionDate;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    // Setters y métodos de comportamiento

    public function setStatus(EnrollmentStatus $status): self
    {
        $this->status = $status;
        
        // Actualizar fechas según el estado
        if ($status->isDropped() && !$this->dropDate) {
            $this->dropDate = date('Y-m-d H:i:s');
        } elseif ($status->isCompleted() && !$this->completionDate) {
            $this->completionDate = date('Y-m-d H:i:s');
        }
        
        return $this;
    }

    public function setFinalGrade(?float $finalGrade): self
    {
        if ($finalGrade !== null && ($finalGrade < 0 || $finalGrade > 100)) {
            throw new \InvalidArgumentException('La calificación final debe estar entre 0 y 100');
        }
        $this->finalGrade = $finalGrade;
        
        // Auto-determinar letra de calificación
        if ($finalGrade !== null) {
            $this->setLetterGrade($this->calculateLetterGrade($finalGrade));
        }
        
        return $this;
    }

    public function setLetterGrade(?string $letterGrade): self
    {
        if ($letterGrade && !preg_match('/^[A-F][+-]?$|^S$|^NS$/', $letterGrade)) {
            throw new \InvalidArgumentException('Formato de letra de calificación inválido');
        }
        $this->letterGrade = $letterGrade;
        return $this;
    }

    public function setCreditsEarned(float $creditsEarned): self
    {
        if ($creditsEarned < 0) {
            throw new \InvalidArgumentException('Los créditos ganados no pueden ser negativos');
        }
        $this->creditsEarned = $creditsEarned;
        return $this;
    }

    public function setAttendancePercentage(float $attendancePercentage): self
    {
        if ($attendancePercentage < 0 || $attendancePercentage > 100) {
            throw new \InvalidArgumentException('El porcentaje de asistencia debe estar entre 0 y 100');
        }
        $this->attendancePercentage = $attendancePercentage;
        return $this;
    }

    public function setPaymentStatus(PaymentStatus $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;
        
        // Actualizar fecha de pago si se marca como pagado
        if ($paymentStatus->isPaid() && !$this->paymentDate) {
            $this->paymentDate = date('Y-m-d H:i:s');
        }
        
        return $this;
    }

    public function setPaymentAmount(float $paymentAmount): self
    {
        if ($paymentAmount < 0) {
            throw new \InvalidArgumentException('El monto de pago no puede ser negativo');
        }
        $this->paymentAmount = $paymentAmount;
        return $this;
    }

    public function setPaymentDate(?string $paymentDate): self
    {
        if ($paymentDate && !strtotime($paymentDate)) {
            throw new \InvalidArgumentException('La fecha de pago no es válida');
        }
        $this->paymentDate = $paymentDate;
        return $this;
    }

    public function setDropDate(?string $dropDate): self
    {
        if ($dropDate && !strtotime($dropDate)) {
            throw new \InvalidArgumentException('La fecha de retiro no es válida');
        }
        $this->dropDate = $dropDate;
        return $this;
    }

    public function setCompletionDate(?string $completionDate): self
    {
        if ($completionDate && !strtotime($completionDate)) {
            throw new \InvalidArgumentException('La fecha de completación no es válida');
        }
        $this->completionDate = $completionDate;
        return $this;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function setMetadataValue(string $key, $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    // Comportamientos del dominio

    public function enroll(): self
    {
        $this->status = EnrollmentStatus::enrolled();
        return $this;
    }

    public function drop(): self
    {
        $this->status = EnrollmentStatus::dropped();
        $this->dropDate = date('Y-m-d H:i:s');
        return $this;
    }

    public function withdraw(): self
    {
        $this->status = EnrollmentStatus::withdrawn();
        $this->dropDate = date('Y-m-d H:i:s');
        return $this;
    }

    public function complete(float $finalGrade, float $creditsEarned): self
    {
        $this->status = EnrollmentStatus::completed();
        $this->finalGrade = $finalGrade;
        $this->creditsEarned = $creditsEarned;
        $this->completionDate = date('Y-m-d H:i:s');
        return $this;
    }

    public function fail(float $finalGrade): self
    {
        $this->status = EnrollmentStatus::failed();
        $this->finalGrade = $finalGrade;
        $this->creditsEarned = 0.0;
        $this->completionDate = date('Y-m-d H:i:s');
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }

    public function isFailed(): bool
    {
        return $this->status->isFailed();
    }

    public function isDropped(): bool
    {
        return $this->status->isDropped();
    }

    public function isWithdrawn(): bool
    {
        return $this->status->isWithdrawn();
    }

    public function hasFinalGrade(): bool
    {
        return $this->finalGrade !== null;
    }

    public function isPassing(): bool
    {
        return $this->finalGrade !== null && $this->finalGrade >= 60;
    }

    public function isFailing(): bool
    {
        return $this->finalGrade !== null && $this->finalGrade < 60;
    }

    public function hasGoodAttendance(): bool
    {
        return $this->attendancePercentage >= 75;
    }

    public function isPaymentComplete(): bool
    {
        return $this->paymentStatus->isPaid() || $this->paymentStatus->isWaived();
    }

    public function hasPayment(): bool
    {
        return $this->paymentAmount > 0;
    }

    public function needsPayment(): bool
    {
        return $this->paymentStatus->needsPayment();
    }

    public function isOverdue(): bool
    {
        return $this->paymentStatus->isOverdue();
    }

    private function calculateLetterGrade(float $finalGrade): string
    {
        if ($finalGrade >= 90) return 'A';
        if ($finalGrade >= 80) return 'B';
        if ($finalGrade >= 70) return 'C';
        if ($finalGrade >= 60) return 'D';
        return 'F';
    }

    public function calculateGPA(): float
    {
        if (!$this->hasFinalGrade()) {
            return 0.0;
        }

        return match($this->letterGrade) {
            'A+' => 4.0,
            'A' => 4.0,
            'A-' => 3.7,
            'B+' => 3.3,
            'B' => 3.0,
            'B-' => 2.7,
            'C+' => 2.3,
            'C' => 2.0,
            'C-' => 1.7,
            'D+' => 1.3,
            'D' => 1.0,
            'D-' => 0.7,
            'F' => 0.0,
            default => 0.0
        };
    }

    /**
     * Convertir a array para transporte
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'student_id' => $this->studentId->getValue(),
            'course_id' => $this->courseId->getValue(),
            'academic_period_id' => $this->academicPeriodId->getValue(),
            'enrollment_date' => $this->enrollmentDate,
            'status' => $this->status->getValue(),
            'final_grade' => $this->finalGrade,
            'letter_grade' => $this->letterGrade,
            'credits_earned' => $this->creditsEarned,
            'attendance_percentage' => $this->attendancePercentage,
            'payment_status' => $this->paymentStatus->getValue(),
            'payment_amount' => $this->paymentAmount,
            'payment_date' => $this->paymentDate,
            'drop_date' => $this->dropDate,
            'completion_date' => $this->completionDate,
            'notes' => $this->notes,
            'gpa' => $this->calculateGPA(),
            'is_passing' => $this->isPassing(),
            'has_good_attendance' => $this->hasGoodAttendance(),
            'is_payment_complete' => $this->isPaymentComplete(),
            'metadata' => $this->metadata,
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
            'deleted_at' => $this->getDeletedAt(),
        ];
    }

    /**
     * Comparar igualdad
     */
    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    /**
     * Representación string del objeto
     */
    public function __toString(): string
    {
        return sprintf(
            'Inscripción %s (%s)',
            $this->id->getValue(),
            $this->status->getValue()
        );
    }
}
