<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
      --secondary: #FFAB40;
      --accent: #8D6E63;
      --light: #FFF8F0;
    }

    * {
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', sans-serif;
      background: var(--light);
      margin: 0;
      padding: 20px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }
    
    h1, h2 {
      text-align: center;
      color: var(--primary);
      margin-bottom: 20px;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px auto;
      background: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    table th, table td {
      padding: 12px;
      border: 1px solid var(--accent);
      text-align: center;
    }
    
    table th {
      background: var(--primary);
      color: var(--light);
      font-weight: bold;
    }

    table tr:nth-child(even) {
      background: #f2f2f2;
    }
    
    a {
      color: var(--secondary);
      text-decoration: none;
      font-weight: bold;
    }
    
    a:hover {
      text-decoration: underline;
    }
    
    p {
      text-align: center;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Panel de Administración</h1>
    <h2>Registros de la Bitácora</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Fecha y Hora</th>
        <th>Tabla Afectada</th>
        <th>Transacción</th>
      </tr>

      <?php while ($row = sqlsrv_fetch_array($stmt_bitacora, SQLSRV_FETCH_ASSOC)) { ?>
          <tr>
              <td><?php echo htmlspecialchars($row['Id_reg']); ?></td>
              <td><?php echo htmlspecialchars($row['Usuario_sistema']); ?></td>
              <td><?php echo htmlspecialchars($row['Fecha_hora_sistema']->format('Y-m-d H:i:s')); ?></td>
              <td><?php echo htmlspecialchars($row['Nombre_tabla']); ?></td>
              <td><?php echo htmlspecialchars($row['Transaccion']); ?></td>
          </tr>
      <?php } ?>
    </table>
    <p><a href="index.php">Volver</a></p>
  </div>
</body>
</html>

