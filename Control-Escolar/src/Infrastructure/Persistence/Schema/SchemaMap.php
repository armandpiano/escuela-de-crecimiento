<?php
/**
 * =============================================================================
 * SCHEMA MAP - INFRASTRUCTURE LAYER
 * Christian LMS System - Arquitectura Hexagonal
 * =============================================================================
 */

namespace ChristianLMS\Infrastructure\Persistence\Schema;

class SchemaMap
{
    /** @var array */
    private static $tableMap = [
        'academic_periods' => 'terms',
        'courses' => 'courses',
        'course_teachers' => 'course_teachers',
        'enrollments' => 'enrollments',
        'modules' => 'modules',
        'subjects' => 'subjects',
        'subject_prerequisites' => 'subject_prerequisites',
        'users' => 'users',
    ];

    /** @var array */
    private static $columnMap = [
        'terms' => [
            'registration_start' => 'inscriptions_start',
            'registration_end' => 'inscriptions_end',
            'start_date' => 'term_start',
            'end_date' => 'term_end',
            'enrollment_start_date' => 'enrollment_start',
            'enrollment_end_date' => 'enrollment_end',
        ],
        'courses' => [
            'max_students' => 'capacity',
            'schedule' => 'schedule_label',
            'virtual_link' => 'zoom_url',
        ],
        'subjects' => [
            'status' => 'is_active',
        ],
        'enrollments' => [
            'enrollment_date' => 'enrollment_at',
            'payment_amount' => 'total_amount',
        ],
        'users' => [
            'full_name' => 'name',
            'password' => 'password_hash',
        ],
    ];

    /** @var array */
    private static $columns = [
        'terms' => [
            'id', 'code', 'name', 'start_date', 'inscriptions_start', 'inscriptions_end',
            'term_start', 'term_end', 'status', 'created_at', 'updated_at', 'enrollment_start',
            'enrollment_end',
        ],
        'courses' => [
            'id', 'term_id', 'subject_id', 'group_name', 'schedule_label', 'modality',
            'zoom_url', 'pdf_path', 'capacity', 'status', 'created_at', 'updated_at',
        ],
        'course_teachers' => [
            'id', 'course_id', 'teacher_id', 'role_in_course', 'created_at',
        ],
        'enrollments' => [
            'id', 'student_id', 'course_id', 'enrollment_at', 'status', 'payment_status',
            'total_amount', 'paid_amount', 'notes', 'created_at', 'updated_at',
        ],
        'modules' => [
            'id', 'code', 'name', 'sort_order', 'is_active', 'created_at', 'updated_at',
        ],
        'subjects' => [
            'id', 'code', 'name', 'module_id', 'module', 'description', 'is_active',
            'created_at', 'updated_at',
        ],
        'subject_prerequisites' => [
            'subject_id', 'prerequisite_subject_id', 'created_at',
        ],
        'users' => [
            'id', 'name', 'email', 'password_hash', 'google_id', 'role', 'status',
            'created_at', 'updated_at',
        ],
    ];

    public static function table(string $domainTable): string
    {
        return self::$tableMap[$domainTable] ?? $domainTable;
    }

    public static function column(string $table, string $column): string
    {
        $table = self::table($table);
        if (isset(self::$columnMap[$table][$column])) {
            return self::$columnMap[$table][$column];
        }

        return $column;
    }

    public static function hasColumn(string $table, string $column): bool
    {
        $table = self::table($table);
        $column = self::column($table, $column);
        return in_array($column, self::$columns[$table] ?? [], true);
    }

    public static function columns(string $table): array
    {
        $table = self::table($table);
        return self::$columns[$table] ?? [];
    }
}
