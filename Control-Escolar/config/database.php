<?php
/**
 * Configuración de Base de Datos - Control Escolar Christian LMS
 * CONFIGURADO según los datos proporcionados
 * 
 * CREDENCIALES REALES CONFIGURADAS:
 */

return [
    'driver' => 'mysql',
    'host' => 'localhost',              // Tu servidor de BD
    'port' => '3306',                  // Puerto MySQL por defecto
    'dbname' => 'control-escolar',     // Nombre real de tu base de datos
    'username' => 'root',              // Tu usuario MySQL
    'password' => '',                  // Tu contraseña MySQL (vacía)
    'charset' => 'utf8',               // Charset compatible con hosting compartido
    'collation' => 'utf8_general_ci',  // Collation optimizada
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8 COLLATE utf8_general_ci"
    ]
];

/**
 * CREDENCIALES ACTUALES:
 * 
 * 'host' => 'localhost',
 * 'dbname' => 'control-escolar',     // ← Tu BD real
 * 'username' => 'root',              // ← Tu usuario real
 * 'password' => '',                  // ← Sin contraseña
 * 
 * CREDENCIALES DEL SISTEMA:
 * - Usuario: admin@christianlms.com
 * - Contraseña: password
 * 
 * URLS QUE FUNCIONAN:
 * ✅ http://localhost/escuela-de-crecimiento/Control-Escolar/
 * ✅ http://localhost/escuela-de-crecimiento/Control-Escolar/login
 * ✅ http://localhost/escuela-de-crecimiento/Control-Escolar/dashboard
 * 
 * EL SISTEMA DETECTARÁ AUTOMÁTICAMENTE:
 * ✅ Si está en localhost/escuela-de-crecimiento/Control-Escolar/
 * ✅ Si está en /Control-Escolar/ (raíz del dominio)
 * ✅ Si el usuario está logueado o no
 * ✅ Redireccionará automáticamente a login o dashboard
 */