<?php
session_start();
include_once '../Logic/db.php';

// Verificar que se reciba el ID de la mascota a visualizar
if (!isset($_GET['mascotaID'])) {
    echo "<p>Error: No se especificó la mascota a visualizar.</p>";
    exit;
}

$mascotaID = $_GET['mascotaID'];

// Consulta a la base de datos para obtener la información de la mascota
$sql = "SELECT * FROM Mascota WHERE MascotaID = ?";
$params = [$mascotaID];
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    echo "<p>Error al obtener la información de la mascota.</p>";
    print_r(sqlsrv_errors());
    exit;
}

$mascota = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$mascota) {
    echo "<p>Mascota no encontrada.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Información de la Mascota - HogarParaTodos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --primary: #2E7D32;
      --secondary: #FFAB40;
      --accent: #8D6E63;
      --light: #FFF8F0;
      --text: #333;
    }
    body {
      font-family: 'Segoe UI', sans-serif;
      background: var(--light);
      margin: 0;
      padding: 20px;
      color: var(--text);
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      border-radius: 12px;
      box-shadow: 0 3px 15px rgba(0,0,0,0.08);
      padding: 20px;
    }
    .pet-image {
      width: 100%;
      max-height: 400px;
      object-fit: cover;
      border-radius: 12px;
    }
    .pet-details {
      margin-top: 20px;
    }
    .pet-details h2 {
      color: var(--primary);
      margin-bottom: 10px;
    }
    .pet-details p {
      margin: 8px 0;
      line-height: 1.4;
    }
    .action-btn {
      display: inline-block;
      margin-top: 1rem;
      padding: 0.8rem 1.5rem;
      background: var(--secondary);
      color: white;
      text-decoration: none;
      border-radius: 8px;
      transition: background 0.3s ease;
    }
    .action-btn:hover {
      background: #FF8F00;
    }
    .back-btn {
      display: inline-block;
      margin-top: 2rem;
      padding: 0.8rem 1.5rem;
      background: var(--primary);
      color: white;
      text-decoration: none;
      border-radius: 8px;
      transition: background 0.3s ease;
    }
    .back-btn:hover {
      background: var(--secondary);
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Información de la Mascota</h2>
    <?php if (!empty($mascota['FotoURL'])) { ?>
      <img class="pet-image" src="<?php echo htmlspecialchars($mascota['FotoURL']); ?>" alt="Foto de <?php echo htmlspecialchars($mascota['Nombre']); ?>">
    <?php } ?>
    <div class="pet-details">
      <p><strong>Nombre:</strong> <?php echo htmlspecialchars($mascota['Nombre']); ?></p>
      <p><strong>Especie:</strong> <?php echo htmlspecialchars($mascota['Especie']); ?></p>
      <p><strong>Raza:</strong> <?php echo htmlspecialchars($mascota['Raza']); ?></p>
      <p><strong>Edad:</strong> <?php echo htmlspecialchars($mascota['Edad']); ?></p>
      <p><strong>Descripción:</strong> <?php echo htmlspecialchars($mascota['Descripcion']); ?></p>
      <p><strong>Estado:</strong> <?php echo htmlspecialchars($mascota['Estado']); ?></p>
      <?php 
        if (isset($mascota['FechaSubida'])) {
            $fecha = is_object($mascota['FechaSubida']) ? $mascota['FechaSubida']->format('Y-m-d H:i:s') : $mascota['FechaSubida'];
            echo "<p><strong>Fecha de Publicación:</strong> " . htmlspecialchars($fecha) . "</p>";
        }
      ?>
    </div>
    
    <!-- Botones de Acción -->
    <?php
    if (isset($_SESSION['usuario'])) {
        // Si el usuario está logueado
        if ($_SESSION['tipo'] === 'admin') {
            // Botón para administradores: Editar Información
            echo '<a href="editar_mascota.php?mascotaID=' . $mascota['MascotaID'] . '" class="action-btn" style="background: var(--primary);">✏️ Editar Información</a>';
        } else {
            // Botón para usuarios: Solicitar Adopción si la mascota está disponible
            if (strtolower($mascota['Estado']) == 'disponible') {
                echo '<a href="solicitar_adopcion.php?mascotaID=' . $mascota['MascotaID'] . '" class="action-btn">🏠 Solicitar Adopción</a>';
            }
        }
    } else {
        // Si no se ha iniciado sesión, mostrar botón para ir al login
        echo '<a href="login.php?mensaje=debe_iniciar_sesion" class="action-btn">Inicia sesión para solicitar adopción</a>';
    }
    ?>
    
    <a href="lista_mascotas.php" class="back-btn">← Volver a Mascotas</a>
  </div>
</body>
</html>
