<?php
/**
 * =============================================================================
 * SERVICIO: COURSE TEACHER SERVICE - APPLICATION LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Application\Services;

use ChristianLMS\Infrastructure\Repositories\CourseTeacherRepository;

class CourseTeacherService
{
    private CourseTeacherRepository $repository;

    public function __construct(CourseTeacherRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getCoursesForTeacher(int $teacherId): array
    {
        return $this->repository->findCoursesByTeacher($teacherId);
    }

    public function assignTeachers(int $courseId, array $teacherIds): void
    {
        $this->repository->assignTeachers($courseId, $teacherIds);
    }

    public function teacherHasConflict(int $teacherId, int $academicPeriodId, string $dayOfWeek, string $startTime, string $endTime, ?int $ignoreCourseId = null): bool
    {
        return $this->repository->hasScheduleConflict($teacherId, $academicPeriodId, $dayOfWeek, $startTime, $endTime, $ignoreCourseId);
    }
}
