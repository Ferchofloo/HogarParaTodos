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

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

include_once '../Logic/db.php';

$usuarioID = $_SESSION['usuarioID'];

// **Consultar información del usuario**
$sql_usuario = "SELECT Nombre, Email, Telefono, Direccion FROM Usuario WHERE UsuarioID = ?";
$params_usuario = [$usuarioID];
$stmt_usuario = sqlsrv_query($conn, $sql_usuario, $params_usuario);
$usuario = sqlsrv_fetch_array($stmt_usuario, SQLSRV_FETCH_ASSOC);

// **Consultar mascotas adoptadas o con solicitud de adopción por el usuario**
// Se usa INNER JOIN para obtener directamente el estado de adopción (EstadoAdopcion)
// en lugar del campo Estado de la tabla Mascota.
$sql_mascotas = "SELECT m.MascotaID, m.Nombre, a.EstadoAdopcion AS Estado 
                 FROM Mascota m
                 INNER JOIN Adopcion a ON m.MascotaID = a.MascotaID
                 WHERE a.UsuarioID = ?";
$params_mascotas = [$usuarioID];
$stmt_mascotas = sqlsrv_query($conn, $sql_mascotas, $params_mascotas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <style>
        :root {
            --primary: #2E7D32;
            --secondary: #FFAB40;
            --accent: #8D6E63;
            --light: #FFF8F0;
            --text: #333;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            padding: 2rem;
            background: var(--light);
            color: var(--text);
        }

        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            padding: 2rem;
        }

        h1 {
            color: var(--primary);
            border-bottom: 3px solid var(--secondary);
            padding-bottom: 0.5rem;
            margin-bottom: 2rem;
        }

        .user-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            background: #f8f8f8;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
        }

        .info-card strong {
            color: var(--primary);
            display: block;
            margin-bottom: 0.5rem;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin: 2rem 0;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .edit-btn {
            background: var(--secondary);
            color: white;
        }

        .logout-btn {
            background: #d32f2f;
            color: white;
        }

        .pets-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .pets-table th,
        .pets-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .pets-table th {
            background: var(--primary);
            color: white;
        }

        .pets-table tr:hover {
            background: #f5f5f5;
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .profile-container {
                padding: 1.5rem;
            }
            
            .pets-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>👤 Perfil de Usuario</h1>
        
        <div class="user-info">
            <div class="info-card">
                <strong>Nombre</strong>
                <?php echo htmlspecialchars($usuario['Nombre']); ?>
            </div>
            <div class="info-card">
                <strong>Correo</strong>
                <?php echo htmlspecialchars($usuario['Email']); ?>
            </div>
            <div class="info-card">
                <strong>Teléfono</strong>
                <?php echo htmlspecialchars($usuario['Telefono'] ?? 'No registrado'); ?>
            </div>
            <div class="info-card">
                <strong>Dirección</strong>
                <?php echo htmlspecialchars($usuario['Direccion'] ?? 'No registrada'); ?>
            </div>
        </div>

        <div class="actions">
            <a href="editar_perfil.php" class="btn edit-btn">✏️ Editar Información</a>
            <a href="logout.php" class="btn logout-btn">🚪 Cerrar Sesión</a>
        </div>

        <h2>🐾 Mascotas Adoptadas / Solicitudes</h2>
        <table class="pets-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = sqlsrv_fetch_array($stmt_mascotas, SQLSRV_FETCH_ASSOC)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['MascotaID']); ?></td>
                        <td><?php echo htmlspecialchars($row['Nombre']); ?></td>
                        <td>
                            <span class="status-badge"><?php echo htmlspecialchars($row['Estado']); ?></span>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <a href="index.php" class="back-link">← Volver al inicio</a>
    </div>
</body>
</html>