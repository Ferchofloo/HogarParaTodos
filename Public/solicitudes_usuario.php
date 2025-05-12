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

// **Consultar solicitudes de adopción del usuario**
$sql = "SELECT a.AdopcionID, m.Nombre AS Mascota, a.FechaAdopcion, a.EstadoAdopcion
        FROM Adopcion a
        JOIN Mascota m ON a.MascotaID = m.MascotaID
        WHERE a.UsuarioID = ?";
$params = [$usuarioID];
$stmt = sqlsrv_query($conn, $sql, $params);

if (!$stmt) {
    die("<p style='color:red;'>Error al obtener solicitudes: " . print_r(sqlsrv_errors(), true) . "</p>");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Solicitudes de Adopción</title>
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

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.2rem;
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }

        .requests-table th,
        .requests-table td {
            padding: 1.2rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .requests-table th {
            background: var(--primary);
            color: white;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .requests-table tr:hover {
            background: #f8f8f8;
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .aprobado { background: #C8E6C9; color: #2E7D32; }
        .en-proceso { background: #FFF3E0; color: #EF6C00; }
        .rechazado { background: #FFCDD2; color: #D32F2F; }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.8rem 1.5rem;
            background: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .back-link:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .requests-table {
                display: block;
                overflow-x: auto;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Mis Solicitudes de Adopción</h1>

        <table class="requests-table">
            <thead>
                <tr>
                    <th>Mascota</th>
                    <th>Fecha de Solicitud</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Mascota']); ?></td>
                        <td><?php echo htmlspecialchars($row['FechaAdopcion']->format('Y-m-d')); ?></td>
                        <td>
                            <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $row['EstadoAdopcion'])); ?>">
                                <?php echo htmlspecialchars($row['EstadoAdopcion']); ?>
                            </span>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <a href="index.php" class="back-link">← Volver al Inicio</a>
    </div>
</body>
</html>
