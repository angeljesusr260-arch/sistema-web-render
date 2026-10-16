<?php
// ============================================================
// CONEXIÓN A BASE DE DATOS MEDIANTE VARIABLES DE ENTORNO
// Buenas prácticas de seguridad: Sin contraseñas en código duro
// ============================================================

// Desactivar excepciones automáticas para manejar el error limpiamente
mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv('DB_HOST');
$port = (int) getenv('DB_PORT');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$name = getenv('DB_NAME');

// Si no se definió DB_PORT, se usa el puerto estándar de MySQL
if ($port <= 0) {
    $port = 3306;
}

$conexion = false;
$error_conexion = "";

// Comprobación de que las variables de entorno existan
if (!$host || !$user || !$name) {
    $error_env = "Faltan variables de entorno en Render (revisa DB_HOST, DB_USER, DB_NAME).";
} else {
    try {
        $conexion = new mysqli($host, $user, $pass, $name, $port);
        if ($conexion->connect_errno) {
            $error_conexion = "No se pudo conectar a Railway (" . $conexion->connect_errno . "): " . $conexion->connect_error;
            $conexion = false;
        } else {
            $conexion->set_charset("utf8mb4");
        }
    } catch (Throwable $e) {
        $error_conexion = "Error al conectar a Railway: " . $e->getMessage();
        $conexion = false;
    }
}
?>
