<?php
/**
 * =============================================================================
 * USE CASE: CREATE COURSE
 * Christian LMS System - Application Layer
 * =============================================================================
 */

namespace ChristianLMS\Application\UseCases\Course;

use ChristianLMS\Application\DTOs\CourseDTOs;
use ChristianLMS\Application\Services\ApplicationServices;
use ChristianLMS\Domain\Entities\Course;
use ChristianLMS\Domain\Ports\CourseRepositoryInterface;
use ChristianLMS\Domain\ValueObjects\{
    CourseId,
    CourseCode,
    CourseStatus,
    UserId,
    SubjectId
};
use ChristianLMS\Infrastructure\Mail\EmailService;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;

/**
 * Use Case: Create Course
 * 
 * Caso de uso para crear un nuevo curso en el sistema
 */
class CreateCourseUseCase
{
    /** @var CourseRepositoryInterface */
    private $courseRepository;
    /** @var ApplicationServices */
    private $applicationServices;
    /** @var EmailService */
    private $emailService;

    public function __construct(
        CourseRepositoryInterface $courseRepository,
        ApplicationServices $applicationServices,
        EmailService $emailService
    ) {
        $this->courseRepository = $courseRepository;
        $this->applicationServices = $applicationServices;
        $this->emailService = $emailService;
    }

    /**
     * Ejecutar caso de uso
     */
    public function execute(CreateCourseRequest $request): CreateCourseResponse
    {
        try {
            // Validar datos de entrada
            $this->validateRequest($request);

            // Verificar que el código del curso sea único
            $courseCode = new CourseCode($request->getCode());
            if ($this->courseRepository->existsByCode($courseCode)) {
                return new CreateCourseResponse(false, 'El código del curso ya existe');
            }

            // Verificar que el profesor existe y es válido
            $professorId = UserId::fromString($request->getProfessorId());
            $professor = $this->applicationServices->getUserRepository()->findById($professorId);
            if (!$professor || (!$professor->isTeacher() && !$professor->isAdmin())) {
                return new CreateCourseResponse(false, 'El profesor especificado no es válido');
            }

            // Verificar que la materia existe (si se especifica)
            $subjectId = null;
            if ($request->getSubjectId()) {
                $subjectId = new SubjectId($request->getSubjectId());
                $subject = $this->applicationServices->getSubjectRepository()->findById($subjectId);
                if (!$subject) {
                    return new CreateCourseResponse(false, 'La materia especificada no existe');
                }
            }

            // Crear el curso
            $course = Course::create(
                $request->getName(),
                $request->getCode(),
                $request->getProfessorId(),
                $request->getSubjectId()
            );

            // Aplicar datos adicionales
            $course->setDescription($request->getDescription());
            $course->setMaxStudents($request->getMaxStudents());
            $course->setCredits($request->getCredits());
            $course->setHoursPerWeek($request->getHoursPerWeek());
            $course->setStartDate($request->getStartDate());
            $course->setEndDate($request->getEndDate());
            $course->setSchedule($request->getSchedule());
            $course->setIsVirtual($request->isVirtual());
            $course->setVirtualPlatform($request->getVirtualPlatform());
            $course->setVirtualLink($request->getVirtualLink());
            $course->setLearningObjectives($request->getLearningObjectives());
            $course->setSyllabus($request->getSyllabus());
            $course->setAssessmentMethods($request->getAssessmentMethods());
            $course->setPrerequisites($request->getPrerequisites());
            $course->setMaterials($request->getMaterials());
            $course->setGradingScale($request->getGradingScale());

            // Establecer estado inicial
            $course->setStatus(CourseStatus::draft());

            // Guardar en repositorio
            $savedCourse = $this->courseRepository->save($course);

            // Enviar notificación al profesor
            try {
                $this->sendProfessorNotification($professor, $savedCourse);
            } catch (\Exception $e) {
                // Log error but don't fail the operation
                error_log('Error sending professor notification: ' . $e->getMessage());
            }

            return new CreateCourseResponse(true, 'Curso creado exitosamente', $savedCourse);

        } catch (\InvalidArgumentException $e) {
            return new CreateCourseResponse(false, 'Datos inválidos: ' . $e->getMessage());
        } catch (DatabaseException $e) {
            return new CreateCourseResponse(false, 'Error de base de datos: ' . $e->getMessage());
        } catch (\Exception $e) {
            return new CreateCourseResponse(false, 'Error interno del servidor');
        }
    }

    /**
     * Validar datos de la petición
     */
    private function validateRequest(CreateCourseRequest $request): void
    {
        if (empty(trim($request->getName()))) {
            throw new \InvalidArgumentException('El nombre del curso es requerido');
        }

        if (empty(trim($request->getCode()))) {
            throw new \InvalidArgumentException('El código del curso es requerido');
        }

        if (empty(trim($request->getProfessorId()))) {
            throw new \InvalidArgumentException('El profesor es requerido');
        }

        if ($request->getMaxStudents() < 1) {
            throw new \InvalidArgumentException('El número máximo de estudiantes debe ser mayor a 0');
        }

        if ($request->getCredits() < 0) {
            throw new \InvalidArgumentException('Los créditos no pueden ser negativos');
        }

        if ($request->getHoursPerWeek() < 0) {
            throw new \InvalidArgumentException('Las horas por semana no pueden ser negativas');
        }

        // Validar fechas si se proporcionan
        if ($request->getStartDate() && !strtotime($request->getStartDate())) {
            throw new \InvalidArgumentException('La fecha de inicio no es válida');
        }

        if ($request->getEndDate() && !strtotime($request->getEndDate())) {
            throw new \InvalidArgumentException('La fecha de fin no es válida');
        }

        // Validar que la fecha de fin sea posterior a la de inicio
        if ($request->getStartDate() && $request->getEndDate()) {
            if (strtotime($request->getEndDate()) <= strtotime($request->getStartDate())) {
                throw new \InvalidArgumentException('La fecha de fin debe ser posterior a la fecha de inicio');
            }
        }
    }

    /**
     * Enviar notificación al profesor
     */
    private function sendProfessorNotification($professor, Course $course): void
    {
        $subject = 'Nuevo Curso Creado';
        $message = sprintf(
            "Hola %s,\n\nSe ha creado un nuevo curso:\n\n" .
            "Nombre: %s\n" .
            "Código: %s\n" .
            "Descripción: %s\n\n" .
            "Puedes gestionar este curso desde tu dashboard.",
            $professor->getFirstName(),
            $course->getName(),
            $course->getCode()->getValue(),
            $course->getDescription() ?? 'Sin descripción'
        );

        $this->emailService->sendEmail(
            $professor->getEmailString(),
            $subject,
            $message
        );
    }
}

/**
 * Request DTO para CreateCourseUseCase
 */
class CreateCourseRequest
{
    /** @var string */
    private $name;
    /** @var string */
    private $code;
    /** @var string */
    private $professorId;
    /** @var string|null */
    private $subjectId;
    /** @var string|null */
    private $description;
    /** @var int */
    private $maxStudents;
    /** @var float */
    private $credits;
    /** @var float */
    private $hoursPerWeek;
    /** @var string|null */
    private $startDate;
    /** @var string|null */
    private $endDate;
    /** @var array|null */
    private $schedule;
    /** @var bool */
    private $isVirtual;
    /** @var string|null */
    private $virtualPlatform;
    /** @var string|null */
    private $virtualLink;
    /** @var string|null */
    private $learningObjectives;
    /** @var string|null */
    private $syllabus;
    /** @var string|null */
    private $assessmentMethods;
    /** @var array|null */
    private $prerequisites;
    /** @var array|null */
    private $materials;
    /** @var array|null */
    private $gradingScale;

    // Getters
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getProfessorId(): string { return $this->professorId; }
    public function getSubjectId(): ?string { return $this->subjectId; }
    public function getDescription(): ?string { return $this->description; }
    public function getMaxStudents(): int { return $this->maxStudents; }
    public function getCredits(): float { return $this->credits; }
    public function getHoursPerWeek(): float { return $this->hoursPerWeek; }
    public function getStartDate(): ?string { return $this->startDate; }
    public function getEndDate(): ?string { return $this->endDate; }
    public function getSchedule(): ?array { return $this->schedule; }
    public function isVirtual(): bool { return $this->isVirtual; }
    public function getVirtualPlatform(): ?string { return $this->virtualPlatform; }
    public function getVirtualLink(): ?string { return $this->virtualLink; }
    public function getLearningObjectives(): ?string { return $this->learningObjectives; }
    public function getSyllabus(): ?string { return $this->syllabus; }
    public function getAssessmentMethods(): ?string { return $this->assessmentMethods; }
    public function getPrerequisites(): ?array { return $this->prerequisites; }
    public function getMaterials(): ?array { return $this->materials; }
    public function getGradingScale(): ?array { return $this->gradingScale; }

    // Setters
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setCode(string $code): self { $this->code = $code; return $this; }
    public function setProfessorId(string $professorId): self { $this->professorId = $professorId; return $this; }
    public function setSubjectId(?string $subjectId): self { $this->subjectId = $subjectId; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setMaxStudents(int $maxStudents): self { $this->maxStudents = $maxStudents; return $this; }
    public function setCredits(float $credits): self { $this->credits = $credits; return $this; }
    public function setHoursPerWeek(float $hoursPerWeek): self { $this->hoursPerWeek = $hoursPerWeek; return $this; }
    public function setStartDate(?string $startDate): self { $this->startDate = $startDate; return $this; }
    public function setEndDate(?string $endDate): self { $this->endDate = $endDate; return $this; }
    public function setSchedule(?array $schedule): self { $this->schedule = $schedule; return $this; }
    public function setIsVirtual(bool $isVirtual): self { $this->isVirtual = $isVirtual; return $this; }
    public function setVirtualPlatform(?string $virtualPlatform): self { $this->virtualPlatform = $virtualPlatform; return $this; }
    public function setVirtualLink(?string $virtualLink): self { $this->virtualLink = $virtualLink; return $this; }
    public function setLearningObjectives(?string $learningObjectives): self { $this->learningObjectives = $learningObjectives; return $this; }
    public function setSyllabus(?string $syllabus): self { $this->syllabus = $syllabus; return $this; }
    public function setAssessmentMethods(?string $assessmentMethods): self { $this->assessmentMethods = $assessmentMethods; return $this; }
    public function setPrerequisites(?array $prerequisites): self { $this->prerequisites = $prerequisites; return $this; }
    public function setMaterials(?array $materials): self { $this->materials = $materials; return $this; }
    public function setGradingScale(?array $gradingScale): self { $this->gradingScale = $gradingScale; return $this; }
}

/**
 * Response DTO para CreateCourseUseCase
 */
class CreateCourseResponse
{
    /** @var bool */
    private $success;
    /** @var string */
    private $message;
    /** @var Course|null */
    private $course;

    public function __construct(bool $success, string $message, ?Course $course = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->course = $course;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'course' => $this->course ? $this->course->toArray() : null
        ];
    }
}
