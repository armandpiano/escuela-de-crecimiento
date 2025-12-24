<?php
/**
 * =============================================================================
 * SERVICIO: SUBJECT PREREQUISITE SERVICE - APPLICATION LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Application\Services;

use ChristianLMS\Infrastructure\Repositories\SubjectPrerequisiteRepository;

class SubjectPrerequisiteService
{
    private SubjectPrerequisiteRepository $repository;

    public function __construct(SubjectPrerequisiteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPrerequisites(int $subjectId): array
    {
        return $this->repository->findPrerequisites($subjectId);
    }

    public function getAvailableSubjectsForStudent(int $studentId): array
    {
        return $this->repository->findAvailableSubjectIdsForStudent($studentId);
    }
}
