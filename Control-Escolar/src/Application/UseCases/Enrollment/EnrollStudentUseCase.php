<?php
/**
 * =============================================================================
 * USE CASE: ENROLL STUDENT
 * Christian LMS System - Application Layer
 * =============================================================================
 */

namespace ChristianLMS\Application\UseCases\Enrollment;

use ChristianLMS\Application\DTOs\EnrollmentDTOs;
use ChristianLMS\Application\Services\ApplicationServices;
use ChristianLMS\Domain\Entities\Enrollment;
use ChristianLMS\Infrastructure\Mail\EmailService;
use ChristianLMS\Infrastructure\Persistence\Exceptions\DatabaseException;
use ChristianLMS\Domain\ValueObjects\{
    EnrollmentId,
    UserId,
    CourseId,
    AcademicPeriodId,
    EnrollmentStatus,
    PaymentStatus
};

/**
 * Use Case: Enroll Student
 * 
 * Caso de uso para inscribir un estudiante en un curso
 */
class EnrollStudentUseCase
{
    /** @var ApplicationServices */
    private $applicationServices;
    /** @var EmailService */
    private $emailService;

    public function __construct(
        ApplicationServices $applicationServices,
        EmailService $emailService
    ) {
        $this->applicationServices = $applicationServices;
        $this->emailService = $emailService;
    }

    /**
     * Ejecutar caso de uso
     */
    public function execute(EnrollStudentRequest $request): EnrollStudentResponse
    {
        try {
            // Validar datos de entrada
            $this->validateRequest($request);

            // Obtener repositorios
            $enrollmentRepository = $this->applicationServices->getEnrollmentRepository();
            $courseRepository = $this->applicationServices->getCourseRepository();
            $userRepository = $this->applicationServices->getUserRepository();

            // Verificar que el estudiante existe
            $studentId = UserId::fromString($request->getStudentId());
            $student = $userRepository->findById($studentId);
            if (!$student || !$student->isStudent()) {
                return new EnrollStudentResponse(false, 'El estudiante especificado no es válido');
            }

            // Verificar que el curso existe
            $courseId = new CourseId($request->getCourseId());
            $course = $courseRepository->findById($courseId);
            if (!$course) {
                return new EnrollStudentResponse(false, 'El curso especificado no existe');
            }

            // Verificar que el curso esté activo
            if (!$course->isActive()) {
                return new EnrollStudentResponse(false, 'El curso no está disponible para inscripciones');
            }

            // Verificar que hay cupo disponible
            if (!$course->canEnrollStudent()) {
                return new EnrollStudentResponse(false, 'El curso no tiene cupo disponible');
            }

            // Verificar que no esté ya inscrito
            $academicPeriodId = new AcademicPeriodId($request->getAcademicPeriodId());
            $existingEnrollment = $enrollmentRepository->findByStudentAndCourse(
                $studentId, 
                $courseId, 
                $academicPeriodId
            );
            if ($existingEnrollment) {
                return new EnrollStudentResponse(false, 'El estudiante ya está inscrito en este curso');
            }

            // Crear la inscripción
            $enrollment = Enrollment::create(
                $request->getStudentId(),
                $request->getCourseId(),
                $request->getAcademicPeriodId()
            );

            // Aplicar datos adicionales
            $enrollment->setPaymentStatus(new PaymentStatus($request->getPaymentStatus() ?? PaymentStatus::PENDING));
            $enrollment->setPaymentAmount($request->getPaymentAmount() ?? 0.0);
            $enrollment->setNotes($request->getNotes());

            // Guardar inscripción
            $savedEnrollment = $enrollmentRepository->save($enrollment);

            // Actualizar número de estudiantes en el curso
            $course->enrollStudent();
            $courseRepository->save($course);

            // Enviar confirmaciones por email
            try {
                $this->sendConfirmationEmails($student, $course, $savedEnrollment);
            } catch (\Exception $e) {
                // Log error but don't fail the operation
                error_log('Error sending enrollment confirmation emails: ' . $e->getMessage());
            }

            return new EnrollStudentResponse(
                true, 
                'Estudiante inscrito exitosamente', 
                $savedEnrollment
            );

        } catch (\InvalidArgumentException $e) {
            return new EnrollStudentResponse(false, 'Datos inválidos: ' . $e->getMessage());
        } catch (DatabaseException $e) {
            return new EnrollStudentResponse(false, 'Error de base de datos: ' . $e->getMessage());
        } catch (\Exception $e) {
            return new EnrollStudentResponse(false, 'Error interno del servidor');
        }
    }

    /**
     * Validar datos de la petición
     */
    private function validateRequest(EnrollStudentRequest $request): void
    {
        if (empty(trim($request->getStudentId()))) {
            throw new \InvalidArgumentException('El ID del estudiante es requerido');
        }

        if (empty(trim($request->getCourseId()))) {
            throw new \InvalidArgumentException('El ID del curso es requerido');
        }

        if (empty(trim($request->getAcademicPeriodId()))) {
            throw new \InvalidArgumentException('El ID del periodo académico es requerido');
        }

        if ($request->getPaymentAmount() < 0) {
            throw new \InvalidArgumentException('El monto de pago no puede ser negativo');
        }
    }

    /**
     * Enviar emails de confirmación
     */
    private function sendConfirmationEmails($student, $course, $enrollment): void
    {
        // Email al estudiante
        $studentSubject = 'Confirmación de Inscripción';
        $studentMessage = sprintf(
            "Hola %s,\n\n" .
            "Tu inscripción en el curso ha sido confirmada:\n\n" .
            "Curso: %s\n" .
            "Código: %s\n" .
            "Profesor: %s\n" .
            "Fecha de inicio: %s\n\n" .
            "Detalles de pago:\n" .
            "Monto: %s\n" .
            "Estado: %s\n\n" .
            "¡Gracias por inscribirte!",
            $student->getFirstName(),
            $course->getName(),
            $course->getCode()->getValue(),
            'Profesor', // TODO: Obtener nombre del profesor
            $course->getStartDate() ? date('d/m/Y', strtotime($course->getStartDate())) : 'Por definir',
            number_format($enrollment->getPaymentAmount(), 2),
            $enrollment->getPaymentStatus()->getValue()
        );

        $this->emailService->sendEmail(
            $student->getEmailString(),
            $studentSubject,
            $studentMessage
        );

        // Email al profesor (opcional)
        // TODO: Implementar email al profesor cuando se tenga acceso al usuario profesor
    }
}

/**
 * Request DTO para EnrollStudentUseCase
 */
class EnrollStudentRequest
{
    /** @var string */
    private $studentId;
    /** @var string */
    private $courseId;
    /** @var string */
    private $academicPeriodId;
    /** @var string|null */
    private $paymentStatus;
    /** @var float */
    private $paymentAmount;
    /** @var string|null */
    private $notes;

    // Getters
    public function getStudentId(): string { return $this->studentId; }
    public function getCourseId(): string { return $this->courseId; }
    public function getAcademicPeriodId(): string { return $this->academicPeriodId; }
    public function getPaymentStatus(): ?string { return $this->paymentStatus; }
    public function getPaymentAmount(): float { return $this->paymentAmount; }
    public function getNotes(): ?string { return $this->notes; }

    // Setters
    public function setStudentId(string $studentId): self { $this->studentId = $studentId; return $this; }
    public function setCourseId(string $courseId): self { $this->courseId = $courseId; return $this; }
    public function setAcademicPeriodId(string $academicPeriodId): self { $this->academicPeriodId = $academicPeriodId; return $this; }
    public function setPaymentStatus(?string $paymentStatus): self { $this->paymentStatus = $paymentStatus; return $this; }
    public function setPaymentAmount(float $paymentAmount): self { $this->paymentAmount = $paymentAmount; return $this; }
    public function setNotes(?string $notes): self { $this->notes = $notes; return $this; }
}

/**
 * Response DTO para EnrollStudentUseCase
 */
class EnrollStudentResponse
{
    /** @var bool */
    private $success;
    /** @var string */
    private $message;
    /** @var Enrollment|null */
    private $enrollment;

    public function __construct(bool $success, string $message, ?Enrollment $enrollment = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->enrollment = $enrollment;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getEnrollment(): ?Enrollment
    {
        return $this->enrollment;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'enrollment' => $this->enrollment ? $this->enrollment->toArray() : null
        ];
    }
}
