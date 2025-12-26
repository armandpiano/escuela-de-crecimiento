<?php
/**
 * =============================================================================
 * ENTIDAD COURSE - DOMAIN LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\Entities;

use ChristianLMS\Domain\ValueObjects\{
    CourseId,
    CourseCode,
    CourseStatus,
    UserId,
    SubjectId
};
use ChristianLMS\Domain\Entities\Traits\Timestampable;
use ChristianLMS\Domain\Entities\Traits\SoftDeleteable;

/**
 * Entidad Course
 * 
 * Representa un curso en el sistema educativo
 * Cumple con los principios de DDD y arquitectura hexagonal.
 */
class Course
{
    use Timestampable, SoftDeleteable;

    /** @var CourseId */
    private $id;
    /** @var string */
    private $name;
    /** @var CourseCode */
    private $code;
    /** @var string|null */
    private $description;
    /** @var UserId */
    private $professorId;
    /** @var SubjectId|null */
    private $subjectId;
    /** @var string|null */
    private $academicPeriodId;
    /** @var int */
    private $maxStudents;
    /** @var int */
    private $currentStudents;
    /** @var string|null */
    private $startDate;
    /** @var string|null */
    private $endDate;
    /** @var array|null */
    private $schedule;
    /** @var float */
    private $credits;
    /** @var float */
    private $hoursPerWeek;
    /** @var CourseStatus */
    private $status;
    /** @var bool */
    private $isVirtual;
    /** @var string|null */
    private $virtualPlatform;
    /** @var string|null */
    private $virtualLink;
    /** @var array|null */
    private $prerequisites;
    /** @var string|null */
    private $learningObjectives;
    /** @var string|null */
    private $syllabus;
    /** @var array|null */
    private $materials;
    /** @var string|null */
    private $assessmentMethods;
    /** @var array|null */
    private $gradingScale;
    /** @var array */
    private $metadata= [];

    /**
     * Constructor
     */
    public function __construct(
        CourseId $id,
        string $name,
        CourseCode $code,
        UserId $professorId,
        ?SubjectId $subjectId = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->professorId = $professorId;
        $this->subjectId = $subjectId;
        $this->maxStudents = 50;
        $this->currentStudents = 0;
        $this->credits = 0.0;
        $this->hoursPerWeek = 0.0;
        $this->status = CourseStatus::draft();
        $this->isVirtual = false;
    }

    /**
     * Crear nuevo curso
     */
    public static function create(
        string $name,
        string $code,
        string $professorId,
        ?string $subjectId = null
    ): self {
        $course = new self(
            CourseId::generate(),
            $name,
            new CourseCode($code),
            UserId::fromString($professorId),
            $subjectId ? new SubjectId($subjectId) : null
        );
        
        return $course;
    }

    // Getters

    public function getId(): CourseId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): CourseCode
    {
        return $this->code;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getProfessorId(): UserId
    {
        return $this->professorId;
    }

    public function getSubjectId(): ?SubjectId
    {
        return $this->subjectId;
    }

    public function getAcademicPeriodId(): ?string
    {
        return $this->academicPeriodId;
    }

    public function getMaxStudents(): int
    {
        return $this->maxStudents;
    }

    public function getCurrentStudents(): int
    {
        return $this->currentStudents;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function getSchedule(): ?array
    {
        return $this->schedule;
    }

    public function getCredits(): float
    {
        return $this->credits;
    }

    public function getHoursPerWeek(): float
    {
        return $this->hoursPerWeek;
    }

    public function getStatus(): CourseStatus
    {
        return $this->status;
    }

    public function isVirtual(): bool
    {
        return $this->isVirtual;
    }

    public function getVirtualPlatform(): ?string
    {
        return $this->virtualPlatform;
    }

    public function getVirtualLink(): ?string
    {
        return $this->virtualLink;
    }

    public function getPrerequisites(): ?array
    {
        return $this->prerequisites;
    }

    public function getLearningObjectives(): ?string
    {
        return $this->learningObjectives;
    }

    public function getSyllabus(): ?string
    {
        return $this->syllabus;
    }

    public function getMaterials(): ?array
    {
        return $this->materials;
    }

    public function getAssessmentMethods(): ?string
    {
        return $this->assessmentMethods;
    }

    public function getGradingScale(): ?array
    {
        return $this->gradingScale;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    // Setters y métodos de comportamiento

    public function setName(string $name): self
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('El nombre del curso no puede estar vacío');
        }
        $this->name = $name;
        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setProfessorId(UserId $professorId): self
    {
        $this->professorId = $professorId;
        return $this;
    }

    public function setSubjectId(?SubjectId $subjectId): self
    {
        $this->subjectId = $subjectId;
        return $this;
    }

    public function setAcademicPeriodId(?string $academicPeriodId): self
    {
        $this->academicPeriodId = $academicPeriodId;
        return $this;
    }

    public function setMaxStudents(int $maxStudents): self
    {
        if ($maxStudents < 1) {
            throw new \InvalidArgumentException('El número máximo de estudiantes debe ser mayor a 0');
        }
        $this->maxStudents = $maxStudents;
        return $this;
    }

    public function setStartDate(?string $startDate): self
    {
        if ($startDate && !strtotime($startDate)) {
            throw new \InvalidArgumentException('La fecha de inicio no es válida');
        }
        $this->startDate = $startDate;
        return $this;
    }

    public function setEndDate(?string $endDate): self
    {
        if ($endDate && !strtotime($endDate)) {
            throw new \InvalidArgumentException('La fecha de fin no es válida');
        }
        $this->endDate = $endDate;
        return $this;
    }

    public function setSchedule(?array $schedule): self
    {
        $this->schedule = $schedule;
        return $this;
    }

    public function setCredits(float $credits): self
    {
        if ($credits < 0) {
            throw new \InvalidArgumentException('Los créditos no pueden ser negativos');
        }
        $this->credits = $credits;
        return $this;
    }

    public function setHoursPerWeek(float $hoursPerWeek): self
    {
        if ($hoursPerWeek < 0) {
            throw new \InvalidArgumentException('Las horas por semana no pueden ser negativas');
        }
        $this->hoursPerWeek = $hoursPerWeek;
        return $this;
    }

    public function setStatus(CourseStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setVirtual(bool $isVirtual): self
    {
        $this->isVirtual = $isVirtual;
        return $this;
    }

    public function setVirtualPlatform(?string $virtualPlatform): self
    {
        $this->virtualPlatform = $virtualPlatform;
        return $this;
    }

    public function setVirtualLink(?string $virtualLink): self
    {
        $this->virtualLink = $virtualLink;
        return $this;
    }

    public function setPrerequisites(?array $prerequisites): self
    {
        $this->prerequisites = $prerequisites;
        return $this;
    }

    public function setLearningObjectives(?string $learningObjectives): self
    {
        $this->learningObjectives = $learningObjectives;
        return $this;
    }

    public function setSyllabus(?string $syllabus): self
    {
        $this->syllabus = $syllabus;
        return $this;
    }

    public function setMaterials(?array $materials): self
    {
        $this->materials = $materials;
        return $this;
    }

    public function setAssessmentMethods(?string $assessmentMethods): self
    {
        $this->assessmentMethods = $assessmentMethods;
        return $this;
    }

    public function setGradingScale(?array $gradingScale): self
    {
        $this->gradingScale = $gradingScale;
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
        $this->status = CourseStatus::active();
        return $this;
    }

    public function deactivate(): self
    {
        $this->status = CourseStatus::cancelled();
        return $this;
    }

    public function complete(): self
    {
        $this->status = CourseStatus::completed();
        return $this;
    }

    public function archive(): self
    {
        $this->status = CourseStatus::archived();
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function hasAvailableSpots(): bool
    {
        return $this->currentStudents < $this->maxStudents;
    }

    public function canEnrollStudent(): bool
    {
        return $this->isActive() && $this->hasAvailableSpots();
    }

    public function enrollStudent(): self
    {
        if (!$this->hasAvailableSpots()) {
            throw new \DomainException('El curso no tiene cupo disponible');
        }
        
        $this->currentStudents++;
        return $this;
    }

    public function unenrollStudent(): self
    {
        if ($this->currentStudents > 0) {
            $this->currentStudents--;
        }
        return $this;
    }

    public function isVirtual(): bool
    {
        return $this->isVirtual;
    }

    public function getAvailableSpots(): int
    {
        return max(0, $this->maxStudents - $this->currentStudents);
    }

    public function getOccupancyPercentage(): float
    {
        if ($this->maxStudents === 0) {
            return 0.0;
        }
        
        return round(($this->currentStudents / $this->maxStudents) * 100, 2);
    }

    public function isFull(): bool
    {
        return $this->currentStudents >= $this->maxStudents;
    }

    public function isInProgress(): bool
    {
        $now = date('Y-m-d');
        return $this->startDate && $this->endDate && 
               $this->startDate <= $now && $this->endDate >= $now;
    }

    public function hasStarted(): bool
    {
        return $this->startDate && $this->startDate <= date('Y-m-d');
    }

    public function hasEnded(): bool
    {
        return $this->endDate && $this->endDate < date('Y-m-d');
    }

    public function isUpcoming(): bool
    {
        return $this->startDate && $this->startDate > date('Y-m-d');
    }

    /**
     * Convertir a array para transporte
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'name' => $this->name,
            'code' => $this->code->getValue(),
            'description' => $this->description,
            'professor_id' => $this->professorId->getValue(),
            'subject_id' => $this->subjectId ? $this->subjectId->getValue() : null,
            'academic_period_id' => $this->academicPeriodId,
            'max_students' => $this->maxStudents,
            'current_students' => $this->currentStudents,
            'available_spots' => $this->getAvailableSpots(),
            'occupancy_percentage' => $this->getOccupancyPercentage(),
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'schedule' => $this->schedule,
            'credits' => $this->credits,
            'hours_per_week' => $this->hoursPerWeek,
            'status' => $this->status->getValue(),
            'is_virtual' => $this->isVirtual,
            'virtual_platform' => $this->virtualPlatform,
            'virtual_link' => $this->virtualLink,
            'prerequisites' => $this->prerequisites,
            'learning_objectives' => $this->learningObjectives,
            'syllabus' => $this->syllabus,
            'materials' => $this->materials,
            'assessment_methods' => $this->assessmentMethods,
            'grading_scale' => $this->gradingScale,
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
        return $this->name . ' (' . $this->code->getValue() . ')';
    }
}
