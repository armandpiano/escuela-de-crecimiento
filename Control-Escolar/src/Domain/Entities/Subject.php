<?php
/**
 * =============================================================================
 * ENTIDAD SUBJECT - DOMAIN LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Domain\Entities;

use ChristianLMS\Domain\ValueObjects\{
    SubjectId,
    SubjectCode,
    SubjectStatus,
    GradeLevel
};
use ChristianLMS\Domain\Entities\Traits\Timestampable;
use ChristianLMS\Domain\Entities\Traits\SoftDeleteable;

/**
 * Entidad Subject
 * 
 * Representa una materia/asignatura en el sistema educativo
 * Cumple con los principios de DDD y arquitectura hexagonal.
 */
class Subject
{
    use Timestampable, SoftDeleteable;

    private SubjectId $id;
    private string $name;
    private SubjectCode $code;
    private ?string $description;
    private ?string $department;
    private ?GradeLevel $gradeLevel;
    private bool $isCore;
    private float $credits;
    private float $hoursPerWeek;
    private ?array $prerequisites;
    private ?string $learningOutcomes;
    private ?string $bibliography;
    private ?array $resources;
    private SubjectStatus $status;
    private array $metadata = [];

    /**
     * Constructor
     */
    public function __construct(
        SubjectId $id,
        string $name,
        SubjectCode $code
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->isCore = true;
        $this->credits = 0.0;
        $this->hoursPerWeek = 0.0;
        $this->status = SubjectStatus::active();
    }

    /**
     * Crear nueva materia
     */
    public static function create(
        string $name,
        string $code
    ): self {
        $subject = new self(
            SubjectId::generate(),
            $name,
            new SubjectCode($code)
        );
        
        return $subject;
    }

    // Getters

    public function getId(): SubjectId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): SubjectCode
    {
        return $this->code;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function getGradeLevel(): ?GradeLevel
    {
        return $this->gradeLevel;
    }

    public function isCore(): bool
    {
        return $this->isCore;
    }

    public function getCredits(): float
    {
        return $this->credits;
    }

    public function getHoursPerWeek(): float
    {
        return $this->hoursPerWeek;
    }

    public function getPrerequisites(): ?array
    {
        return $this->prerequisites;
    }

    public function getLearningOutcomes(): ?string
    {
        return $this->learningOutcomes;
    }

    public function getBibliography(): ?string
    {
        return $this->bibliography;
    }

    public function getResources(): ?array
    {
        return $this->resources;
    }

    public function getStatus(): SubjectStatus
    {
        return $this->status;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    // Setters y métodos de comportamiento

    public function setName(string $name): self
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('El nombre de la materia no puede estar vacío');
        }
        $this->name = $name;
        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;
        return $this;
    }

    public function setGradeLevel(?GradeLevel $gradeLevel): self
    {
        $this->gradeLevel = $gradeLevel;
        return $this;
    }

    public function setIsCore(bool $isCore): self
    {
        $this->isCore = $isCore;
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

    public function setPrerequisites(?array $prerequisites): self
    {
        $this->prerequisites = $prerequisites;
        return $this;
    }

    public function setLearningOutcomes(?string $learningOutcomes): self
    {
        $this->learningOutcomes = $learningOutcomes;
        return $this;
    }

    public function setBibliography(?string $bibliography): self
    {
        $this->bibliography = $bibliography;
        return $this;
    }

    public function setResources(?array $resources): self
    {
        $this->resources = $resources;
        return $this;
    }

    public function setStatus(SubjectStatus $status): self
    {
        $this->status = $status;
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
        $this->status = SubjectStatus::active();
        return $this;
    }

    public function deactivate(): self
    {
        $this->status = SubjectStatus::inactive();
        return $this;
    }

    public function deprecate(): self
    {
        $this->status = SubjectStatus::deprecated();
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isInactive(): bool
    {
        return $this->status->isInactive();
    }

    public function isDeprecated(): bool
    {
        return $this->status->isDeprecated();
    }

    public function hasPrerequisites(): bool
    {
        return !empty($this->prerequisites);
    }

    public function hasResources(): bool
    {
        return !empty($this->resources);
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
            'department' => $this->department,
            'grade_level' => $this->gradeLevel?->getValue(),
            'is_core' => $this->isCore,
            'credits' => $this->credits,
            'hours_per_week' => $this->hoursPerWeek,
            'prerequisites' => $this->prerequisites,
            'learning_outcomes' => $this->learningOutcomes,
            'bibliography' => $this->bibliography,
            'resources' => $this->resources,
            'status' => $this->status->getValue(),
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
