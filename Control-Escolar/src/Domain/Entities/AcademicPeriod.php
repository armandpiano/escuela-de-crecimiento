<?php
/**
 * =============================================================================
 * ENTIDAD ACADEMIC PERIOD - DOMAIN LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\Entities;

use ChristianLMS\Domain\ValueObjects\{
    AcademicPeriodId,
    AcademicPeriodType
};
use ChristianLMS\Domain\Entities\Traits\Timestampable;
use ChristianLMS\Domain\Entities\Traits\SoftDeleteable;

/**
 * Entidad AcademicPeriod
 * 
 * Representa un periodo académico en el sistema educativo
 * Cumple con los principios de DDD y arquitectura hexagonal.
 */
class AcademicPeriod
{
    use Timestampable, SoftDeleteable;

    private AcademicPeriodId $id;
    private string $name;
    private string $code;
    private AcademicPeriodType $type;
    private string $startDate;
    private string $endDate;
    private ?string $registrationStart;
    private ?string $registrationEnd;
    private int $academicYear;
    private int $periodNumber;
    private bool $isActive;
    private bool $isCurrent;
    private int $maxStudentsPerCourse;
    private ?string $gradingDeadline;
    private ?string $transcriptReleaseDate;
    private ?string $notes;
    private array $metadata = [];

    /**
     * Constructor
     */
    public function __construct(
        AcademicPeriodId $id,
        string $name,
        string $code,
        AcademicPeriodType $type,
        string $startDate,
        string $endDate,
        int $academicYear,
        int $periodNumber
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->type = $type;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->academicYear = $academicYear;
        $this->periodNumber = $periodNumber;
        $this->isActive = false;
        $this->isCurrent = false;
        $this->maxStudentsPerCourse = 50;
    }

    /**
     * Crear nuevo periodo académico
     */
    public static function create(
        string $name,
        string $code,
        string $type,
        string $startDate,
        string $endDate,
        int $academicYear,
        int $periodNumber
    ): self {
        $period = new self(
            AcademicPeriodId::generate(),
            $name,
            $code,
            new AcademicPeriodType($type),
            $startDate,
            $endDate,
            $academicYear,
            $periodNumber
        );
        
        return $period;
    }

    // Getters

    public function getId(): AcademicPeriodId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getType(): AcademicPeriodType
    {
        return $this->type;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function getRegistrationStart(): ?string
    {
        return $this->registrationStart;
    }

    public function getRegistrationEnd(): ?string
    {
        return $this->registrationEnd;
    }

    public function getAcademicYear(): int
    {
        return $this->academicYear;
    }

    public function getPeriodNumber(): int
    {
        return $this->periodNumber;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function getMaxStudentsPerCourse(): int
    {
        return $this->maxStudentsPerCourse;
    }

    public function getGradingDeadline(): ?string
    {
        return $this->gradingDeadline;
    }

    public function getTranscriptReleaseDate(): ?string
    {
        return $this->transcriptReleaseDate;
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

    public function setName(string $name): self
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('El nombre del periodo no puede estar vacío');
        }
        $this->name = $name;
        return $this;
    }

    public function setCode(string $code): self
    {
        $code = trim($code);
        if (empty($code)) {
            throw new \InvalidArgumentException('El código del periodo no puede estar vacío');
        }
        $this->code = $code;
        return $this;
    }

    public function setType(AcademicPeriodType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setStartDate(string $startDate): self
    {
        if (!strtotime($startDate)) {
            throw new \InvalidArgumentException('La fecha de inicio no es válida');
        }
        $this->startDate = $startDate;
        return $this;
    }

    public function setEndDate(string $endDate): self
    {
        if (!strtotime($endDate)) {
            throw new \InvalidArgumentException('La fecha de fin no es válida');
        }
        $this->endDate = $endDate;
        return $this;
    }

    public function setRegistrationStart(?string $registrationStart): self
    {
        if ($registrationStart && !strtotime($registrationStart)) {
            throw new \InvalidArgumentException('La fecha de inicio de inscripciones no es válida');
        }
        $this->registrationStart = $registrationStart;
        return $this;
    }

    public function setRegistrationEnd(?string $registrationEnd): self
    {
        if ($registrationEnd && !strtotime($registrationEnd)) {
            throw new \InvalidArgumentException('La fecha de fin de inscripciones no es válida');
        }
        $this->registrationEnd = $registrationEnd;
        return $this;
    }

    public function setAcademicYear(int $academicYear): self
    {
        if ($academicYear < 1900 || $academicYear > 2100) {
            throw new \InvalidArgumentException('El año académico debe estar entre 1900 y 2100');
        }
        $this->academicYear = $academicYear;
        return $this;
    }

    public function setPeriodNumber(int $periodNumber): self
    {
        if ($periodNumber < 1) {
            throw new \InvalidArgumentException('El número de periodo debe ser mayor a 0');
        }
        $this->periodNumber = $periodNumber;
        return $this;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function setIsCurrent(bool $isCurrent): self
    {
        $this->isCurrent = $isCurrent;
        return $this;
    }

    public function setMaxStudentsPerCourse(int $maxStudentsPerCourse): self
    {
        if ($maxStudentsPerCourse < 1) {
            throw new \InvalidArgumentException('El número máximo de estudiantes por curso debe ser mayor a 0');
        }
        $this->maxStudentsPerCourse = $maxStudentsPerCourse;
        return $this;
    }

    public function setGradingDeadline(?string $gradingDeadline): self
    {
        if ($gradingDeadline && !strtotime($gradingDeadline)) {
            throw new \InvalidArgumentException('La fecha límite de calificaciones no es válida');
        }
        $this->gradingDeadline = $gradingDeadline;
        return $this;
    }

    public function setTranscriptReleaseDate(?string $transcriptReleaseDate): self
    {
        if ($transcriptReleaseDate && !strtotime($transcriptReleaseDate)) {
            throw new \InvalidArgumentException('La fecha de liberación de calificaciones no es válida');
        }
        $this->transcriptReleaseDate = $transcriptReleaseDate;
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

    public function activate(): self
    {
        $this->isActive = true;
        return $this;
    }

    public function deactivate(): self
    {
        $this->isActive = false;
        $this->isCurrent = false;
        return $this;
    }

    public function setAsCurrent(): self
    {
        $this->isCurrent = true;
        $this->isActive = true;
        return $this;
    }

    public function unsetAsCurrent(): self
    {
        $this->isCurrent = false;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isUpcoming(): bool
    {
        return $this->startDate > date('Y-m-d');
    }

    public function isInProgress(): bool
    {
        $now = date('Y-m-d');
        return $this->startDate <= $now && $this->endDate >= $now;
    }

    public function hasEnded(): bool
    {
        return $this->endDate < date('Y-m-d');
    }

    public function isRegistrationOpen(): bool
    {
        $now = date('Y-m-d');
        return $this->registrationStart && $this->registrationEnd &&
               $this->registrationStart <= $now && $this->registrationEnd >= $now;
    }

    public function isRegistrationOpenNow(): bool
    {
        if (!$this->registrationStart || !$this->registrationEnd) {
            return false;
        }
        
        $now = date('Y-m-d H:i:s');
        return $this->registrationStart <= $now && $this->registrationEnd >= $now;
    }

    public function getDurationInDays(): int
    {
        $start = new \DateTime($this->startDate);
        $end = new \DateTime($this->endDate);
        return $end->diff($start)->days;
    }

    public function getDurationInWeeks(): float
    {
        return round($this->getDurationInDays() / 7, 1);
    }

    public function getWeeksUntilStart(): int
    {
        if ($this->isUpcoming()) {
            $start = new \DateTime($this->startDate);
            $now = new \DateTime();
            return $start->diff($now)->days / 7;
        }
        return 0;
    }

    public function getWeeksRemaining(): int
    {
        if ($this->isInProgress()) {
            $end = new \DateTime($this->endDate);
            $now = new \DateTime();
            return $end->diff($now)->days / 7;
        }
        return 0;
    }

    /**
     * Convertir a array para transporte
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type->getValue(),
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'enrollment_start' => $this->registrationStart,
            'enrollment_end' => $this->registrationEnd,
            'status' => $this->isActive ? 'active' : 'inactive',
            'academic_year' => $this->academicYear,
            'period_number' => $this->periodNumber,
            'is_active' => $this->isActive,
            'is_current' => $this->isCurrent,
            'max_students_per_course' => $this->maxStudentsPerCourse,
            'grading_deadline' => $this->gradingDeadline,
            'transcript_release_date' => $this->transcriptReleaseDate,
            'notes' => $this->notes,
            'duration_days' => $this->getDurationInDays(),
            'duration_weeks' => $this->getDurationInWeeks(),
            'is_upcoming' => $this->isUpcoming(),
            'is_in_progress' => $this->isInProgress(),
            'has_ended' => $this->hasEnded(),
            'is_registration_open' => $this->isRegistrationOpen(),
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
        return $this->name . ' (' . $this->academicYear . '-' . $this->periodNumber . ')';
    }
}
