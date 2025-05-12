<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if(!isset($_SESSION['usuario'])){
    header("Location: login.php?mensaje=error");
    exit;
}

if (!isset($_SESSION['usuarioID']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include_once '../Logic/db.php';

// **Consultar registros de la bitácora**
$sql_bitacora = "SELECT Id_reg, Usuario_sistema, Fecha_hora_sistema, Nombre_tabla, Transaccion 
                 FROM Bitacora ORDER BY Fecha_hora_sistema DESC";
$stmt_bitacora = sqlsrv_query($conn, $sql_bitacora);

if (!$stmt_bitacora) {
    die("<p style='color:red;'>Error al obtener registros de la bitácora: " . print_r(sqlsrv_errors(), true) . "</p>");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --primary: #2E7D32;
      --secondary: #607D8B;
      --accent: #FFAB40;
      --light: #F5F5F5;
      --text: #263238;
    }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      margin: 0;
      padding: 2rem;
      background: var(--light);
      color: var(--text);
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 2rem;
    }

    h1 {
      color: var(--primary);
      text-align: center;
      margin-bottom: 1.5rem;
      font-size: 2.4rem;
      border-bottom: 3px solid var(--accent);
      padding-bottom: 0.5rem;
    }

    h2 {
      color: var(--secondary);
      margin: 2rem 0 1.5rem;
      font-size: 1.8rem;
    }

    .log-table {
      width: 100%;
      border-collapse: collapse;
      margin: 1rem 0;
      overflow: hidden;
      border-radius: 8px;
    }

    .log-table th,
    .log-table td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #ECEFF1;
    }

    .log-table th {
      background: var(--primary);
      color: white;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .log-table tr:nth-child(even) {
      background: #F5F5F5;
    }

    .log-table tr:hover {
      background: #ECEFF1;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      margin-top: 2rem;
      padding: 0.8rem 1.5rem;
      background: var(--accent);
      color: white;
      text-decoration: none;
      border-radius: 6px;
      transition: transform 0.3s ease;
    }

    .back-link:hover {
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      body {
        padding: 1rem;
      }
      
      .container {
        padding: 1.5rem;
      }
      
      .log-table {
        display: block;
        overflow-x: auto;
      }
      
      h1 {
        font-size: 2rem;
      }
      
      h2 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>📋 Panel de Administración</h1>
    <h2>🕒 Registros de la Bitácora</h2>
    
    <table class="log-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Usuario</th>
          <th>Fecha y Hora</th>
          <th>Tabla Afectada</th>
          <th>Transacción</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = sqlsrv_fetch_array($stmt_bitacora, SQLSRV_FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['Id_reg']); ?></td>
                <td><?php echo htmlspecialchars($row['Usuario_sistema']); ?></td>
                <td><?php echo htmlspecialchars($row['Fecha_hora_sistema']->format('Y-m-d H:i:s')); ?></td>
                <td><?php echo htmlspecialchars($row['Nombre_tabla']); ?></td>
                <td><?php echo htmlspecialchars($row['Transaccion']); ?></td>
            </tr>
        <?php } ?>
      </tbody>
    </table>

    <a href="index.php" class="back-link">← Volver al Panel</a>
  </div>
</body>
</html>

