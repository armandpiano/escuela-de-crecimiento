<?php
/**
 * Punto de entrada principal - Control-Escolar
 * PROCESAMIENTO DIRECTO sin redirecciones
 * Funciona en CUALQUIER ubicación
 */

// Interceptar la petición antes de que se procese
if (!defined('CONTROL_ESCOLAR_ENTRY_POINT')) {
    define('CONTROL_ESCOLAR_ENTRY_POINT', true);

    // Detectar la ruta base automáticamente (compatible con subcarpetas)
    $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $basePath = rtrim($basePath, '/');
    if ($basePath === '/' || $basePath === '.') {
        $basePath = '';
    }
    define('BASE_PATH', $basePath);

    // Ejecutar el archivo principal
    require __DIR__ . '/public/index.php';
}
?>
