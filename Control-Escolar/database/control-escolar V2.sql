-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-01-2026 a las 23:49:42
-- Versión del servidor: 10.1.38-MariaDB
-- Versión de PHP: 7.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `control-escolar`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `academic_periods`
--

CREATE TABLE `academic_periods` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` enum('quarter','semester','year','custom') NOT NULL DEFAULT 'quarter',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `enrollment_start_date` date DEFAULT NULL,
  `enrollment_end_date` date DEFAULT NULL,
  `status` enum('draft','active','inactive','archived') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activities`
--

CREATE TABLE `activities` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `type` enum('file_task','text_task','presential_evidence') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'file_task',
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructions` text COLLATE utf8mb4_unicode_ci,
  `publish_status` enum('draft','published','scheduled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `publish_at` datetime DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `allow_late` tinyint(1) NOT NULL DEFAULT '0',
  `max_points` decimal(10,2) NOT NULL DEFAULT '100.00',
  `settings_text` longtext COLLATE utf8mb4_unicode_ci,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `status` enum('present','late','absent','justified') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'absent',
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkin_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attendance_sessions`
--

CREATE TABLE `attendance_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `session_at` datetime NOT NULL,
  `type` enum('zoom','presencial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'zoom',
  `topic` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_text` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `term_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `group_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schedule_label` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modality` enum('zoom','presencial','mixto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'zoom',
  `zoom_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `status` enum('open','closed','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `courses`
--

INSERT INTO `courses` (`id`, `term_id`, `subject_id`, `group_name`, `schedule_label`, `modality`, `zoom_url`, `pdf_path`, `capacity`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'AMP-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'closed', '2025-12-26 20:26:50', '2025-12-26 20:26:50'),
(2, 2, 2, 'MEM-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'open', '2025-12-26 20:26:50', '2025-12-26 20:26:50'),
(3, 2, 3, 'MAD-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'open', '2025-12-26 20:26:50', '2026-01-07 00:23:03'),
(4, 4, 1, 'AMP-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'open', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(5, 4, 2, 'MEM-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'open', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(6, 4, 3, 'MAD-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'open', '2026-01-07 00:03:32', '2026-01-07 00:23:09'),
(7, 4, 4, 'RUD1-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'open', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(8, 4, 5, 'RUD2-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'open', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(9, 4, 6, 'EBI1-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'open', '2026-01-07 00:03:32', '2026-01-07 00:23:11'),
(10, 4, 7, 'AUT1-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'open', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(11, 4, 8, 'AUT2-G1', 'Sábado 09:00–11:00', 'presencial', NULL, NULL, 30, 'open', '2026-01-07 00:03:32', '2026-01-07 00:21:03'),
(12, 4, 9, 'PGAT-LQ', 'Lunes quincenal 20:00–22:00', 'presencial', NULL, NULL, 30, 'open', '2026-01-07 00:56:28', '2026-01-07 01:06:21'),
(13, 4, 9, 'PGAT-DOM', 'Domingos 07:00–08:00', 'presencial', NULL, NULL, 30, 'open', '2026-01-07 00:56:28', '2026-01-07 01:06:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `course_teachers`
--

CREATE TABLE `course_teachers` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `role_in_course` enum('primary','secondary','assistant') NOT NULL DEFAULT 'primary',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `course_teachers`
--

INSERT INTO `course_teachers` (`id`, `course_id`, `teacher_id`, `role_in_course`, `created_at`) VALUES
(1, 1, 2, 'primary', '2025-12-26 20:26:50'),
(2, 1, 3, 'secondary', '2025-12-26 20:26:50'),
(3, 2, 4, 'primary', '2025-12-26 20:26:50'),
(4, 4, 2, 'primary', '2026-01-07 00:03:33'),
(5, 4, 3, 'secondary', '2026-01-07 00:03:33'),
(6, 5, 4, 'primary', '2026-01-07 00:03:33'),
(7, 5, 5, 'secondary', '2026-01-07 00:03:33'),
(8, 5, 12, 'assistant', '2026-01-07 00:03:33'),
(9, 6, 6, 'primary', '2026-01-07 00:03:33'),
(10, 6, 26, 'secondary', '2026-01-07 00:03:33'),
(11, 7, 13, 'primary', '2026-01-07 00:03:33'),
(12, 7, 14, 'secondary', '2026-01-07 00:03:33'),
(13, 7, 15, 'assistant', '2026-01-07 00:03:33'),
(14, 8, 16, 'primary', '2026-01-07 00:03:33'),
(15, 8, 17, 'secondary', '2026-01-07 00:03:33'),
(16, 9, 18, 'primary', '2026-01-07 00:03:33'),
(17, 9, 19, 'secondary', '2026-01-07 00:03:33'),
(18, 9, 20, 'assistant', '2026-01-07 00:03:33'),
(19, 10, 21, 'primary', '2026-01-07 00:03:33'),
(20, 10, 22, 'secondary', '2026-01-07 00:03:33'),
(21, 11, 23, 'primary', '2026-01-07 00:03:33'),
(22, 11, 24, 'secondary', '2026-01-07 00:03:33'),
(23, 11, 25, 'assistant', '2026-01-07 00:03:33'),
(24, 12, 28, 'primary', '2026-01-07 00:56:28'),
(25, 12, 23, 'secondary', '2026-01-07 00:56:28'),
(26, 13, 13, 'primary', '2026-01-07 00:56:28'),
(27, 13, 23, 'secondary', '2026-01-07 00:56:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `enrollment_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','pending','cancelled','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `payment_status` enum('pending','paid','partial','overdue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrollment_at`, `status`, `payment_status`, `total_amount`, `paid_amount`, `notes`, `created_at`, `updated_at`) VALUES
(10, 32, 7, '2026-01-08 00:19:11', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:19:11-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:19:11', '2026-01-08 00:19:11'),
(11, 33, 12, '2026-01-08 00:37:55', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\",\"Rudimentos II\",\"Autoridad Espiritual I\"],\"modulo2\":[\"Ministerio 301\",\"Panorama Antiguo Testamento\"],\"modulo3\":[\"Ninguna\"],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:37:55-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:37:55', '2026-01-08 00:37:55'),
(12, 34, 8, '2026-01-08 00:41:35', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:41:35-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:41:35', '2026-01-08 00:41:35'),
(13, 35, 5, '2026-01-08 00:42:24', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"Acabo de cursar \\\"Afirmando mis pasos \\\"\"},\"capturado_at\":\"2026-01-07T18:42:24-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:42:24', '2026-01-08 00:42:24'),
(14, 36, 4, '2026-01-08 00:45:30', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Ninguna\"],\"modulo2\":[\"Ninguna\"],\"modulo3\":[\"Ninguna\"],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:45:30-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:45:30', '2026-01-08 00:45:30'),
(15, 37, 5, '2026-01-08 00:48:54', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:48:54-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:48:54', '2026-01-08 00:48:54'),
(16, 38, 5, '2026-01-08 00:52:08', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"101 Membresía\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:52:08-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:52:08', '2026-01-08 00:52:08'),
(17, 39, 7, '2026-01-08 00:52:32', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:52:32-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:52:32', '2026-01-08 00:52:32'),
(18, 40, 11, '2026-01-08 00:54:21', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\",\"Rudimentos II\",\"Autoridad Espiritual I\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:54:21-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:54:21', '2026-01-08 00:54:21'),
(19, 41, 9, '2026-01-08 00:54:45', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\",\"Rudimentos II\",\"Autoridad Espiritual I\",\"Autoridad Espiritual II\"],\"modulo2\":[],\"modulo3\":[\"Ninguna\"],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:54:45-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:54:45', '2026-01-08 00:54:45'),
(20, 42, 7, '2026-01-08 00:56:22', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:56:22-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:56:22', '2026-01-08 00:56:22'),
(21, 43, 11, '2026-01-08 00:57:22', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\",\"Rudimentos II\",\"Autoridad Espiritual I\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:57:22-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:57:22', '2026-01-08 00:57:22'),
(22, 44, 8, '2026-01-08 00:57:39', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:57:39-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:57:39', '2026-01-08 00:57:39'),
(23, 45, 7, '2026-01-08 00:59:12', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T18:59:12-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 00:59:12', '2026-01-08 00:59:12'),
(24, 46, 11, '2026-01-08 01:01:14', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Autoridad Espiritual II\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T19:01:14-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 01:01:14', '2026-01-08 01:01:14'),
(25, 47, 6, '2026-01-08 01:04:14', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\"],\"modulo2\":[\"Ninguna\"],\"modulo3\":[\"Ninguna\"],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T19:04:14-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 01:04:14', '2026-01-08 01:04:14'),
(26, 48, 10, '2026-01-08 01:16:44', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\",\"Rudimentos II\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T19:16:44-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 01:16:44', '2026-01-08 01:16:44'),
(27, 49, 12, '2026-01-08 01:21:50', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\"],\"modulo2\":[\"Panorama Antiguo Testamento\"],\"modulo3\":[\"Grupos Celulares\"],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T19:21:50-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 01:21:50', '2026-01-08 01:21:50'),
(28, 50, 13, '2026-01-08 01:36:56', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[],\"modulo2\":[\"Panorama Antiguo Testamento\"],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T19:36:56-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 01:36:56', '2026-01-08 01:36:56'),
(29, 51, 6, '2026-01-08 01:52:00', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T19:52:00-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 01:52:00', '2026-01-08 01:52:00'),
(30, 52, 5, '2026-01-08 01:53:15', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T19:53:15-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 01:53:15', '2026-01-08 01:53:15'),
(31, 53, 10, '2026-01-08 01:57:30', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Rudimentos I\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T19:57:30-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 01:57:30', '2026-01-08 01:57:30'),
(32, 54, 7, '2026-01-08 02:01:56', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\"],\"modulo2\":[\"Ninguna\"],\"modulo3\":[\"Ninguna\"],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T20:01:56-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 02:01:56', '2026-01-08 02:01:56'),
(33, 55, 10, '2026-01-08 02:57:43', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\",\"Rudimentos II\"],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T20:57:43-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 02:57:43', '2026-01-08 02:57:43'),
(34, 56, 7, '2026-01-08 03:52:06', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[],\"modulo2\":[],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T21:52:06-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 03:52:06', '2026-01-08 03:52:06'),
(35, 57, 11, '2026-01-08 04:26:15', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":0,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\",\"Rudimentos II\",\"Autoridad Espiritual I\",\"Métodos de Estudio Bíblico\"],\"modulo2\":[\"Ministerio 301\",\"Carácter Espiritual I\",\"Carácter Espiritual II\",\"Panorama Antiguo Testamento\",\"Panorama Nuevo Testamento\",\"Evangelismo\"],\"modulo3\":[],\"otras\":\"\"},\"capturado_at\":\"2026-01-07T22:26:15-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 04:26:15', '2026-01-08 04:26:15'),
(36, 58, 12, '2026-01-08 14:40:36', 'active', 'pending', '0.00', '0.00', '{\"manual_fisico\":1,\"historial\":{\"modulo1\":[\"Afirmando mis pasos\",\"101 Membresía\",\"201 Madurez\",\"Rudimentos I\",\"Rudimentos II\",\"Autoridad Espiritual I\",\"Autoridad Espiritual II\",\"Métodos de Estudio Bíblico\"],\"modulo2\":[\"Ministerio 301\",\"Carácter Espiritual I\",\"Carácter Espiritual II\"],\"modulo3\":[],\"otras\":\"Libertad en Cristo\"},\"capturado_at\":\"2026-01-08T08:40:36-06:00\",\"source\":\"landing_inscripciones\"}', '2026-01-08 14:40:36', '2026-01-08 14:40:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enrollment_final_grades`
--

CREATE TABLE `enrollment_final_grades` (
  `enrollment_id` int(10) UNSIGNED NOT NULL,
  `final_grade` decimal(5,2) NOT NULL DEFAULT '0.00',
  `passed` tinyint(1) NOT NULL DEFAULT '0',
  `computed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grades`
--

CREATE TABLE `grades` (
  `id` int(10) UNSIGNED NOT NULL,
  `activity_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `points` decimal(10,2) NOT NULL DEFAULT '0.00',
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `graded_by` int(10) UNSIGNED NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matricula_sequences`
--

CREATE TABLE `matricula_sequences` (
  `year` smallint(5) UNSIGNED NOT NULL,
  `last_number` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modules`
--

CREATE TABLE `modules` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `student_profiles`
--

CREATE TABLE `student_profiles` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `matricula` varchar(30) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `student_profiles`
--

INSERT INTO `student_profiles` (`user_id`, `matricula`, `phone`, `address`, `birthdate`, `created_at`, `updated_at`) VALUES
(32, NULL, '5554097430', NULL, NULL, '2026-01-08 00:19:11', '2026-01-08 00:19:11'),
(33, NULL, '5533980052', NULL, NULL, '2026-01-08 00:37:55', '2026-01-08 00:37:55'),
(34, NULL, '5528121460', NULL, NULL, '2026-01-08 00:41:35', '2026-01-08 00:41:35'),
(35, NULL, '5631184154', NULL, NULL, '2026-01-08 00:42:24', '2026-01-08 00:42:24'),
(36, NULL, '5523208797', NULL, NULL, '2026-01-08 00:45:30', '2026-01-08 00:45:30'),
(37, NULL, '5537492869', NULL, NULL, '2026-01-08 00:48:54', '2026-01-08 00:48:54'),
(38, NULL, '5543611514', NULL, NULL, '2026-01-08 00:52:08', '2026-01-08 00:52:08'),
(39, NULL, '5519245535', NULL, NULL, '2026-01-08 00:52:32', '2026-01-08 00:52:32'),
(40, NULL, '5531378745', NULL, NULL, '2026-01-08 00:54:21', '2026-01-08 00:54:21'),
(41, NULL, '5578945917', NULL, NULL, '2026-01-08 00:54:45', '2026-01-08 00:54:45'),
(42, NULL, '5620893946', NULL, NULL, '2026-01-08 00:56:22', '2026-01-08 00:56:22'),
(43, NULL, '5548538541', NULL, NULL, '2026-01-08 00:57:22', '2026-01-08 00:57:22'),
(44, NULL, '5546471140', NULL, NULL, '2026-01-08 00:57:39', '2026-01-08 00:57:39'),
(45, NULL, '5547988958', NULL, NULL, '2026-01-08 00:59:12', '2026-01-08 00:59:12'),
(46, NULL, '5578892497', NULL, NULL, '2026-01-08 01:01:14', '2026-01-08 01:01:14'),
(47, NULL, '5533322693', NULL, NULL, '2026-01-08 01:04:14', '2026-01-08 01:04:14'),
(48, NULL, '5575176343', NULL, NULL, '2026-01-08 01:16:44', '2026-01-08 01:16:44'),
(49, NULL, '5568933883', NULL, NULL, '2026-01-08 01:21:50', '2026-01-08 01:21:50'),
(50, NULL, '5628198800', NULL, NULL, '2026-01-08 01:36:56', '2026-01-08 01:36:56'),
(51, NULL, '5561357605', NULL, NULL, '2026-01-08 01:52:00', '2026-01-08 01:52:00'),
(52, NULL, '5561303924', NULL, NULL, '2026-01-08 01:53:15', '2026-01-08 01:53:15'),
(53, NULL, '5568785696', NULL, NULL, '2026-01-08 01:57:30', '2026-01-08 01:57:30'),
(54, NULL, '5525014205', NULL, NULL, '2026-01-08 02:01:56', '2026-01-08 02:01:56'),
(55, NULL, '5519655082', NULL, NULL, '2026-01-08 02:57:43', '2026-01-08 02:57:43'),
(56, NULL, '5574174871', NULL, NULL, '2026-01-08 03:52:06', '2026-01-08 03:52:06'),
(57, NULL, '5523501504', NULL, NULL, '2026-01-08 04:26:15', '2026-01-08 04:26:15'),
(58, NULL, '5617167216', NULL, NULL, '2026-01-08 14:40:36', '2026-01-08 14:40:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `student_subject_history`
--

CREATE TABLE `student_subject_history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `subject_id` int(10) UNSIGNED NOT NULL,
  `enrollment_id` int(10) UNSIGNED DEFAULT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `passed` tinyint(1) NOT NULL DEFAULT '0',
  `passed_at` datetime DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subjects`
--

CREATE TABLE `subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_id` int(10) UNSIGNED DEFAULT NULL,
  `module` enum('I','II','III') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `subjects`
--

INSERT INTO `subjects` (`id`, `code`, `name`, `module_id`, `module`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'AMP', 'Afirmando mis pasos', NULL, '', NULL, 1, '2025-12-26 20:26:50', '2025-12-26 20:26:50'),
(2, 'MEM', 'Membresía', NULL, '', NULL, 1, '2025-12-26 20:26:50', '2025-12-26 20:26:50'),
(3, 'MAD', 'Madurez', NULL, '', NULL, 1, '2025-12-26 20:26:50', '2025-12-26 20:26:50'),
(4, 'Rudimentos I', 'Rudimentos 1', NULL, NULL, 'Rudimentos I', 1, '2025-12-26 21:29:24', '2025-12-26 21:29:24'),
(5, 'RUD2', 'Rudimentos II', NULL, NULL, NULL, 1, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(6, 'EBI1', 'Estudio Bíblico Inductivo I', NULL, NULL, NULL, 1, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(7, 'AUT1', 'Autoridad I', NULL, NULL, NULL, 1, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(8, 'AUT2', 'Autoridad II', NULL, NULL, NULL, 1, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(9, 'PGAT', 'Panorama General del Antiguo Testamento', NULL, NULL, NULL, 1, '2026-01-07 00:56:28', '2026-01-07 00:56:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subject_prerequisites`
--

CREATE TABLE `subject_prerequisites` (
  `subject_id` int(10) UNSIGNED NOT NULL,
  `prerequisite_subject_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `subject_prerequisites`
--

INSERT INTO `subject_prerequisites` (`subject_id`, `prerequisite_subject_id`, `created_at`) VALUES
(2, 1, '2025-12-26 20:26:50'),
(3, 2, '2025-12-26 20:26:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `submissions`
--

CREATE TABLE `submissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `activity_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `status` enum('not_submitted','submitted','late','graded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_submitted',
  `submitted_at` datetime DEFAULT NULL,
  `current_version` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `submission_files`
--

CREATE TABLE `submission_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `submission_version_id` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `submission_versions`
--

CREATE TABLE `submission_versions` (
  `id` int(10) UNSIGNED NOT NULL,
  `submission_id` int(10) UNSIGNED NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `text_content` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `teacher_profiles`
--

CREATE TABLE `teacher_profiles` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `bio` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `teacher_profiles`
--

INSERT INTO `teacher_profiles` (`user_id`, `phone`, `bio`, `created_at`, `updated_at`) VALUES
(2, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(3, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(4, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(5, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(6, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(12, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(13, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(14, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(15, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(16, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(17, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(18, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(19, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(20, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(21, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(22, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(23, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(24, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(25, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(26, NULL, NULL, '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(28, NULL, NULL, '2026-01-07 00:56:28', '2026-01-07 00:56:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `terms`
--

CREATE TABLE `terms` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `inscriptions_start` date NOT NULL,
  `inscriptions_end` date NOT NULL,
  `term_start` date NOT NULL,
  `term_end` date NOT NULL,
  `status` enum('draft','active','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `enrollment_start` date DEFAULT NULL,
  `enrollment_end` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `terms`
--

INSERT INTO `terms` (`id`, `code`, `name`, `start_date`, `inscriptions_start`, `inscriptions_end`, `term_start`, `term_end`, `status`, `created_at`, `updated_at`, `enrollment_start`, `enrollment_end`) VALUES
(1, 'MAY-AGO-2025', 'MAY-AGO 2025', NULL, '2025-04-15', '2025-05-10', '2025-05-10', '2025-08-16', 'closed', '2025-12-26 20:26:50', '2025-12-26 20:26:50', NULL, NULL),
(2, 'SEP-DIC-2025', 'SEP-DIC 2025', NULL, '2025-08-15', '2025-09-06', '2025-09-06', '2025-12-06', '', '2025-12-26 20:26:50', '2026-01-08 16:47:49', '2025-08-01', '2025-08-31'),
(4, 'ENE-ABR-2026', 'ENE-ABR 2026', NULL, '2026-01-06', '2026-01-17', '2026-01-17', '2026-04-25', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','teacher','student','control_escolar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `status` enum('active','inactive','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `google_id`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Administrador General', 'admin@ecafc.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', 'active', '2025-12-26 20:26:40', '2025-12-26 20:26:40'),
(2, 'José Luis Bravo', 'jose.bravo@ecafc.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'teacher', 'active', '2025-12-26 20:26:40', '2025-12-26 20:26:40'),
(3, 'Andrés Martinez', 'andres.martinez@ecafc.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'teacher', 'active', '2025-12-26 20:26:40', '2025-12-26 20:26:40'),
(4, 'Gerardo Bravo', 'gerardo.bravo@ecafc.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'teacher', 'active', '2025-12-26 20:26:40', '2025-12-26 20:26:40'),
(5, 'Adriana Olivier', 'adriana.olivier@ecafc.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'teacher', 'active', '2025-12-26 20:26:40', '2025-12-26 20:26:40'),
(6, 'Martha Beltrán', 'martha.beltran@ecafc.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'teacher', 'active', '2025-12-26 20:26:40', '2025-12-26 20:26:40'),
(12, 'Jesús', 'jesus@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(13, 'Paty', 'paty@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(14, 'Sofi', 'sofi@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(15, 'Erika', 'erika@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(16, 'Brenda', 'brenda@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(17, 'Mario', 'mario@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(18, 'Frank', 'frank@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(19, 'Adrián', 'adrian@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(20, 'Joe', 'joe@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(21, 'Sabás', 'sabas@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(22, 'Héctor', 'hector@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(23, 'Martín', 'martin@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(24, 'Samuel', 'samuel@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(25, 'Leo', 'leo@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(26, 'Armando', 'armando@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:03:32', '2026-01-07 00:03:32'),
(28, 'Joel', 'joel@ecafc.mx', NULL, NULL, 'teacher', 'active', '2026-01-07 00:56:28', '2026-01-07 00:56:28'),
(32, 'Oscar Flores Molina', 'oscarfmolina@yahoo.com.mx', NULL, NULL, 'student', 'active', '2026-01-08 00:19:11', '2026-01-08 00:19:11'),
(33, 'Jaqueline Alcántara Garcia', 'jaky_1205@hotmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:37:55', '2026-01-08 00:37:55'),
(34, 'Angel Rodríguez Marroquín', 'cruzazularm07@hotmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:41:35', '2026-01-08 00:41:35'),
(35, 'Zabdiel Betbirai Mendez Reyes', 'kazary.reyes2610@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:42:24', '2026-01-08 00:42:24'),
(36, 'FERNANDA FERNANDEZ FUENTES', 'fernyfdezfu22@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:45:30', '2026-01-08 00:45:30'),
(37, 'Kami Romero', 'kamii0107@yahoo.com', NULL, NULL, 'student', 'active', '2026-01-08 00:48:54', '2026-01-08 00:48:54'),
(38, 'Andrea Valades Villa', 'andreavalades2488@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:52:08', '2026-01-08 00:52:08'),
(39, 'Jose Israel dominguez ruiz', 'mecanicaferia526@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:52:32', '2026-01-08 00:52:32'),
(40, 'Ana Maria Morales Escalante', 'marianaacereros@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:54:21', '2026-01-08 00:54:21'),
(41, 'Erika Gabriela Santiago García', 'erikagabrielasantiago3@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:54:45', '2026-01-08 00:54:45'),
(42, 'Uri Deborath Feria Ortiz', 'uryferia82@gimail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:56:22', '2026-01-08 00:56:22'),
(43, 'Maria de Jesús Gutierrez Flores', 'chuchisgutierrezf@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:57:22', '2026-01-08 00:57:22'),
(44, 'Jorge Luis Chávez Carballo', 'jlchavezca@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:57:39', '2026-01-08 00:57:39'),
(45, 'Elizabeth Castelan Carmona', 'eliizacas604@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 00:59:12', '2026-01-08 00:59:12'),
(46, 'Joshua Alejandro Yllescas Villafuerte', 'pichuajosh991@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 01:01:14', '2026-01-08 01:01:14'),
(47, 'Ruth Frida López Diaz', 'rfridaldiaz@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 01:04:14', '2026-01-08 01:04:14'),
(48, 'Nora Chávez', 'noraelbachavez@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 01:16:44', '2026-01-08 01:16:44'),
(49, 'Roberto Carlos Cruz Hernández', 'rober103086@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 01:21:50', '2026-01-08 01:21:50'),
(50, 'Gabriela Luviano Guarneros', 'luviano623@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 01:36:56', '2026-01-08 01:36:56'),
(51, 'Armando Vazquez Aguilar', 'arvazagui_0518@hotmail.com', NULL, NULL, 'student', 'active', '2026-01-08 01:52:00', '2026-01-08 01:52:00'),
(52, 'Samuel Sánchez González', 'samysg.12038@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 01:53:15', '2026-01-08 01:53:15'),
(53, 'Virginia Alonso Aguirre', 'lizta1210@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 01:57:30', '2026-01-08 01:57:30'),
(54, 'Leticia Carranza Gutiérrez', 'alegria.lcg62@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 02:01:56', '2026-01-08 02:01:56'),
(55, 'Aurora Maria Luisa Garcia Espinosa', 'aurogaresp@hotmail.com', NULL, NULL, 'student', 'active', '2026-01-08 02:57:43', '2026-01-08 02:57:43'),
(56, 'Guillermina Bautista Picazo', 'guillermina165@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 03:52:06', '2026-01-08 03:52:06'),
(57, 'Juan Jorge Sánchez Espinosa', 'embajador8511@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 04:26:15', '2026-01-08 04:26:15'),
(58, 'Jessica Suberza Vega', 'medicavrimjs@gmail.com', NULL, NULL, 'student', 'active', '2026-01-08 14:40:35', '2026-01-08 14:40:35');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `academic_periods`
--
ALTER TABLE `academic_periods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_period_code` (`code`),
  ADD KEY `idx_period_status` (`status`),
  ADD KEY `idx_period_dates` (`start_date`,`end_date`);

--
-- Indices de la tabla `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_act_course` (`course_id`),
  ADD KEY `idx_act_status` (`publish_status`),
  ADD KEY `idx_act_due` (`due_at`),
  ADD KEY `idx_act_creator` (`created_by`);

--
-- Indices de la tabla `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_session_student` (`session_id`,`student_id`),
  ADD KEY `idx_att_session` (`session_id`),
  ADD KEY `idx_att_student` (`student_id`),
  ADD KEY `idx_att_status` (`status`);

--
-- Indices de la tabla `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sessions_course` (`course_id`),
  ADD KEY `idx_sessions_datetime` (`session_at`),
  ADD KEY `idx_sessions_type` (`type`);

--
-- Indices de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_actor` (`actor_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_entity` (`entity`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indices de la tabla `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_courses_term` (`term_id`),
  ADD KEY `idx_courses_subject` (`subject_id`),
  ADD KEY `idx_courses_status` (`status`),
  ADD KEY `idx_courses_modality` (`modality`);

--
-- Indices de la tabla `course_teachers`
--
ALTER TABLE `course_teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_course_teacher_role` (`course_id`,`teacher_id`,`role_in_course`),
  ADD KEY `idx_ct_course` (`course_id`),
  ADD KEY `idx_ct_teacher` (`teacher_id`);

--
-- Indices de la tabla `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_student_course` (`student_id`,`course_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_enrollment_at` (`enrollment_at`),
  ADD KEY `idx_course_status` (`course_id`,`status`);

--
-- Indices de la tabla `enrollment_final_grades`
--
ALTER TABLE `enrollment_final_grades`
  ADD PRIMARY KEY (`enrollment_id`);

--
-- Indices de la tabla `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_grade_activity_student` (`activity_id`,`student_id`),
  ADD KEY `idx_grade_activity` (`activity_id`),
  ADD KEY `idx_grade_student` (`student_id`),
  ADD KEY `fk_grade_grader` (`graded_by`);

--
-- Indices de la tabla `matricula_sequences`
--
ALTER TABLE `matricula_sequences`
  ADD PRIMARY KEY (`year`);

--
-- Indices de la tabla `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_modules_code` (`code`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_pr_token` (`token`),
  ADD KEY `idx_pr_user` (`user_id`),
  ADD KEY `idx_pr_expires` (`expires_at`);

--
-- Indices de la tabla `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `ux_student_matricula` (`matricula`);

--
-- Indices de la tabla `student_subject_history`
--
ALTER TABLE `student_subject_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_hist_student_subject` (`student_id`,`subject_id`),
  ADD KEY `idx_hist_subject` (`subject_id`),
  ADD KEY `idx_hist_student` (`student_id`),
  ADD KEY `fk_hist_enrollment` (`enrollment_id`);

--
-- Indices de la tabla `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_subjects_code` (`code`),
  ADD KEY `idx_subjects_module` (`module`),
  ADD KEY `idx_subjects_is_active` (`is_active`),
  ADD KEY `idx_subjects_module_id` (`module_id`);

--
-- Indices de la tabla `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  ADD PRIMARY KEY (`subject_id`,`prerequisite_subject_id`),
  ADD KEY `idx_prereq` (`prerequisite_subject_id`);

--
-- Indices de la tabla `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_activity_student` (`activity_id`,`student_id`),
  ADD KEY `idx_sub_activity` (`activity_id`),
  ADD KEY `idx_sub_student` (`student_id`),
  ADD KEY `idx_sub_status` (`status`);

--
-- Indices de la tabla `submission_files`
--
ALTER TABLE `submission_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sf_version` (`submission_version_id`);

--
-- Indices de la tabla `submission_versions`
--
ALTER TABLE `submission_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_sub_version` (`submission_id`,`version`),
  ADD KEY `idx_sv_submission` (`submission_id`);

--
-- Indices de la tabla `teacher_profiles`
--
ALTER TABLE `teacher_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indices de la tabla `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_terms_code` (`code`),
  ADD KEY `idx_terms_status` (`status`),
  ADD KEY `idx_terms_dates` (`term_start`,`term_end`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_users_email` (`email`),
  ADD UNIQUE KEY `ux_users_google_id` (`google_id`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `academic_periods`
--
ALTER TABLE `academic_periods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `course_teachers`
--
ALTER TABLE `course_teachers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `student_subject_history`
--
ALTER TABLE `student_subject_history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `submission_files`
--
ALTER TABLE `submission_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `submission_versions`
--
ALTER TABLE `submission_versions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `terms`
--
ALTER TABLE `terms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `fk_act_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_act_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_att_session` FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `attendance_sessions`
--
ALTER TABLE `attendance_sessions`
  ADD CONSTRAINT `fk_sessions_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_courses_term` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `course_teachers`
--
ALTER TABLE `course_teachers`
  ADD CONSTRAINT `fk_ct_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ct_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enrollments_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_enrollments_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `enrollment_final_grades`
--
ALTER TABLE `enrollment_final_grades`
  ADD CONSTRAINT `fk_efg_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_grade_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_grade_grader` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_grade_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `fk_student_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `student_subject_history`
--
ALTER TABLE `student_subject_history`
  ADD CONSTRAINT `fk_hist_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hist_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hist_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `subject_prerequisites`
--
ALTER TABLE `subject_prerequisites`
  ADD CONSTRAINT `fk_sp_prereq` FOREIGN KEY (`prerequisite_subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `fk_sp_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `fk_sub_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sub_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `submission_files`
--
ALTER TABLE `submission_files`
  ADD CONSTRAINT `fk_sf_version` FOREIGN KEY (`submission_version_id`) REFERENCES `submission_versions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `submission_versions`
--
ALTER TABLE `submission_versions`
  ADD CONSTRAINT `fk_sv_submission` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `teacher_profiles`
--
ALTER TABLE `teacher_profiles`
  ADD CONSTRAINT `fk_teacher_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
