<?php
// ============================================================
// CONEXIÓN A BASE DE DATOS MEDIANTE VARIABLES DE ENTORNO
// Buenas prácticas de seguridad: Sin contraseñas en código duro
// ============================================================

$host = getenv('DB_HOST');
$port = (int) getenv('DB_PORT');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$name = getenv('DB_NAME');

// Si no se definió DB_PORT, se usa el puerto estándar de MySQL
if ($port <= 0) {
    $port = 3306;
}

// Comprobación de que las variables de entorno existan
if (!$host || !$user || !$name) {
    $error_env = "Faltan variables de entorno en Render (DB_HOST, DB_USER, DB_NAME).";
    $conexion = false;
} else {
    // Intentar la conexión con el servidor MySQL (Railway)
    $conexion = @new mysqli($host, $user, $pass, $name, $port);
    
    if ($conexion->connect_error) {
        $error_conexion = "Error de conexión a Railway: " . $conexion->connect_error;
        $conexion = false;
    } else {
        $conexion->set_charset("utf8mb4");
    }
}
?>
