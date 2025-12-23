<?php
/**
 * Punto de entrada principal - Control-Escolar
 * PROCESAMIENTO DIRECTO sin redirecciones
 * Funciona en CUALQUIER ubicación
 */

// Interceptar la petición antes de que se procese
if (!defined('CONTROL_ESCOLAR_ENTRY_POINT')) {
    define('CONTROL_ESCOLAR_ENTRY_POINT', true);
    
    // Detectar la ruta base automáticamente
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // Caso 1: localhost/escuela-de-crecimiento/Control-Escolar/
    if (strpos($requestUri, '/escuela-de-crecimiento/Control-Escolar') !== false) {
        define('BASE_PATH', '/escuela-de-crecimiento/Control-Escolar');
    }
    // Caso 2: /Control-Escolar/ (raíz del dominio)
    elseif (strpos($requestUri, '/Control-Escolar') !== false) {
        define('BASE_PATH', '/Control-Escolar');
    }
    // Caso 3: Cualquier otra ubicación
    else {
        define('BASE_PATH', '/Control-Escolar');
    }
    
    // Ejecutar el archivo principal
    require __DIR__ . '/public/index.php';
}
?>