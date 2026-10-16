<?php
require_once("conexion.php");

$mensaje = "";
$tipo_alerta = "";

// Si la conexión es exitosa, aseguramos que la tabla exista e insertamos si viene un POST
if ($conexion) {
    // 1. Crear tabla usuarios si no existe para asegurar funcionamiento inmediato
    $sql_crear_tabla = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        telefono VARCHAR(30),
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conexion->query($sql_crear_tabla);

    // 2. Insertar datos de prueba iniciales si la tabla está vacía
    $check_vacia = $conexion->query("SELECT COUNT(*) AS total FROM usuarios");
    if ($check_vacia) {
        $fila = $check_vacia->fetch_assoc();
        if ($fila['total'] == 0) {
            $conexion->query("INSERT INTO usuarios (nombre, email, telefono) VALUES 
                ('Ana García', 'ana.garcia@correo.com', '555-1001'),
                ('Luis Pérez', 'luis.perez@correo.com', '555-1002'),
                ('Jesús Ramírez', 'jesus.ramirez@correo.com', '555-1003')");
        }
    }

    // 3. Procesar formulario para agregar nuevo registro
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (!empty($nombre) && !empty($email)) {
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, telefono) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nombre, $email, $telefono);
            if ($stmt->execute()) {
                $mensaje = "¡Registro guardado con éxito en la base de datos de Railway!";
                $tipo_alerta = "success";
            } else {
                $mensaje = "Error al guardar el registro: " . $conexion->error;
                $tipo_alerta = "danger";
            }
            $stmt->close();
        } else {
            $mensaje = "Por favor completa los campos obligatorios (Nombre y Correo).";
            $tipo_alerta = "warning";
        }
    }

    // 4. Obtener todos los registros desde Railway
    $resultado = $conexion->query("SELECT * FROM usuarios ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Despliegue en Render + Railway | UPVM</title>
  <style>
    :root {
      --primary: #4f46e5;
      --primary-hover: #4338ca;
      --success: #16a34a;
      --danger: #dc2626;
      --warning: #d97706;
      --bg: #f8fafc;
      --card-bg: #ffffff;
      --text: #1e293b;
      --text-muted: #64748b;
      --border: #e2e8f0;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    body {
      background-color: var(--bg);
      color: var(--text);
      line-height: 1.6;
      padding: 30px 15px;
    }

    .container {
      max-width: 950px;
      margin: 0 auto;
    }

    header {
      background: var(--card-bg);
      padding: 25px 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      border: 1px solid var(--border);
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
    }

    .header-titles h1 {
      font-size: 1.7rem;
      color: var(--primary);
      margin-bottom: 4px;
    }

    .header-titles p {
      color: var(--text-muted);
      font-size: 0.95rem;
    }

    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 30px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    .status-online {
      background: #dcfce7;
      color: var(--success);
      border: 1px solid #bbf7d0;
    }

    .status-offline {
      background: #fee2e2;
      color: var(--danger);
      border: 1px solid #fecaca;
    }

    .card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 25px 30px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      border: 1px solid var(--border);
      margin-bottom: 25px;
    }

    .card-title {
      font-size: 1.25rem;
      margin-bottom: 15px;
      color: var(--text);
      border-bottom: 2px solid var(--border);
      padding-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .alert {
      padding: 12px 18px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 0.95rem;
    }

    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

    /* Formulario */
    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 15px;
      margin-bottom: 15px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .form-group label {
      font-size: 0.88rem;
      font-weight: 600;
      color: var(--text);
    }

    .form-group input {
      padding: 10px 14px;
      border: 1px solid var(--border);
      border-radius: 6px;
      font-size: 0.95rem;
      outline: none;
      transition: border-color 0.2s;
    }

    .form-group input:focus {
      border-color: var(--primary);
    }

    .btn-submit {
      background: var(--primary);
      color: white;
      border: none;
      padding: 11px 22px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 0.95rem;
      transition: background 0.2s;
    }

    .btn-submit:hover {
      background: var(--primary-hover);
    }

    /* Tabla */
    .table-responsive {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 0.95rem;
    }

    th, td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }

    th {
      background-color: #f1f5f9;
      color: var(--text);
      font-weight: 600;
    }

    tr:hover {
      background-color: #f8fafc;
    }

    .empty-state {
      text-align: center;
      padding: 30px;
      color: var(--text-muted);
    }

    footer {
      text-align: center;
      font-size: 0.88rem;
      color: var(--text-muted);
      margin-top: 30px;
    }
  </style>
</head>
<body>

  <div class="container">

    <!-- Encabezado -->
    <header>
      <div class="header-titles">
        <h1>Sistema Web en Render</h1>
        <p>Publicación con Docker y Base de Datos MySQL en Railway</p>
      </div>

      <div>
        <?php if ($conexion): ?>
          <span class="status-badge status-online">
            ● Base de Datos Conectada (Railway)
          </span>
        <?php else: ?>
          <span class="status-badge status-offline">
            ● Desconectada de la Base
          </span>
        <?php endif; ?>
      </div>
    </header>

    <!-- Alertas si existen -->
    <?php if (!empty($mensaje)): ?>
      <div class="alert alert-<?php echo $tipo_alerta; ?>">
        <?php echo htmlspecialchars($mensaje); ?>
      </div>
    <?php endif; ?>

    <?php if (!$conexion): ?>
      <div class="card">
        <h2 class="card-title" style="color: var(--danger);">Información de Diagnóstico</h2>
        <p style="margin-bottom: 10px;">
          <?php echo isset($error_env) ? htmlspecialchars($error_env) : (isset($error_conexion) ? htmlspecialchars($error_conexion) : "No hay conexión activa."); ?>
        </p>
        <div style="background: #f1f5f9; padding: 15px; border-radius: 6px; font-size: 0.9rem;">
          <strong>Variables esperadas en Render:</strong>
          <ul style="margin-left: 20px; margin-top: 6px;">
            <li><code>DB_HOST</code>: <?php echo getenv('DB_HOST') ? 'Configurada' : 'Falta'; ?></li>
            <li><code>DB_PORT</code>: <?php echo getenv('DB_PORT') ? getenv('DB_PORT') : 'Por defecto 3306'; ?></li>
            <li><code>DB_USER</code>: <?php echo getenv('DB_USER') ? 'Configurada' : 'Falta'; ?></li>
            <li><code>DB_PASS</code>: <?php echo getenv('DB_PASS') ? '****** (Oculta por seguridad)' : 'Falta'; ?></li>
            <li><code>DB_NAME</code>: <?php echo getenv('DB_NAME') ? 'Configurada' : 'Falta'; ?></li>
          </ul>
        </div>
      </div>
    <?php endif; ?>

    <!-- Formulario para insertar datos -->
    <div class="card">
      <h2 class="card-title">Registrar Nuevo Usuario en Railway</h2>
      <form action="index.php" method="POST">
        <input type="hidden" name="accion" value="crear">
        <div class="form-grid">
          <div class="form-group">
            <label for="nombre">Nombre Completo *</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan Pérez" required>
          </div>
          <div class="form-group">
            <label for="email">Correo Electrónico *</label>
            <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required>
          </div>
          <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" placeholder="555-123-4567">
          </div>
        </div>
        <button type="submit" class="btn-submit">Guardar en Base de Datos</button>
      </form>
    </div>

    <!-- Tabla que muestra los datos de la base de datos de Railway -->
    <div class="card">
      <div class="card-title">
        <span>Datos Almacenados en Railway (MySQL)</span>
        <span style="font-size: 0.85rem; font-weight: normal; color: var(--text-muted);">
          Consulta en vivo: <code>SELECT * FROM usuarios</code>
        </span>
      </div>

      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Correo Electrónico</th>
              <th>Teléfono</th>
              <th>Fecha de Registro</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($conexion && $resultado && $resultado->num_rows > 0): ?>
              <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                  <td><strong>#<?php echo htmlspecialchars($fila['id']); ?></strong></td>
                  <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                  <td><?php echo htmlspecialchars($fila['email']); ?></td>
                  <td><?php echo htmlspecialchars($fila['telefono'] ?? 'N/A'); ?></td>
                  <td><?php echo htmlspecialchars($fila['fecha_registro']); ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="empty-state">
                  No hay registros disponibles en la base de datos actualmente.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pie de página con datos del alumno -->
    <footer>
      <p><strong>Alumno:</strong> Jesús Ramírez Figueroa | <strong>Universidad:</strong> UPVM</p>
      <p>Asignatura: Programación Web Orientada a Servicios | Despliegue con Docker en Render</p>
    </footer>

  </div>

</body>
</html>
