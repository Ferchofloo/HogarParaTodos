<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../Logic/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuarioID'])) {
    header("Location: login.php");
    exit;
}

// Validar si el usuario es administrador
if ($_SESSION['tipo'] !== 'admin') {
    echo "<p style='color:red;'>Acceso denegado. No tienes permisos de administrador.</p>";
    echo "<p><a href='index.php'>Volver al inicio</a></p>";
    exit;
}

// Consultar solicitudes de adopción pendientes
$sql = "SELECT a.AdopcionID, a.MascotaID, m.Nombre AS Mascota, u.Nombre AS Usuario, a.FechaAdopcion, a.EstadoAdopcion
        FROM Adopcion a
        JOIN Mascota m ON a.MascotaID = m.MascotaID
        JOIN Usuario u ON a.UsuarioID = u.UsuarioID
        WHERE a.EstadoAdopcion = 'pendiente'";

$stmt = sqlsrv_query($conn, $sql);

if (!$stmt) {
    die("Error al obtener solicitudes: " . print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador</title>
    <style>
        :root {
            --primary: #2E7D32;
            --secondary: #FFAB40;
            --danger: #D32F2F;
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
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            border-bottom: 3px solid var(--secondary);
            padding-bottom: 0.5rem;
        }

        h2 {
            color: var(--primary);
            margin: 2rem 0 1.5rem;
            font-size: 1.8rem;
        }

        .solicitudes-grid {
            display: grid;
            gap: 1.5rem;
        }

        .solicitud {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-left: 4px solid var(--secondary);
        }

        .solicitud p {
            margin: 0.5rem 0;
            font-size: 1rem;
        }

        .solicitud strong {
            color: var(--primary);
            min-width: 140px;
            display: inline-block;
        }

        .acciones {
            margin-top: 1.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .boton {
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: transform 0.3s ease;
        }

        .boton:hover {
            transform: translateY(-2px);
        }

        .rechazo {
            background: var(--danger);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: transform 0.3s ease;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            background: #FFEBEE;
            border-radius: 10px;
            border-left: 4px solid var(--danger);
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 2rem 0;
        }

        .empty-state p {
            margin: 0;
            color: var(--danger);
            font-size: 1.1rem;
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.8rem 1.5rem;
            background: var(--secondary);
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
            
            h1 {
                font-size: 2rem;
            }
            
            .acciones {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐾 Panel de Administrador</h1>

        <h2>📋 Solicitudes de Adopción Pendientes</h2>

        <div class="solicitudes-grid">
            <?php
            $solicitudes_exist = false;

            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $solicitudes_exist = true;
            ?>
                <div class="solicitud">
                    <p><strong>Mascota:</strong> <?php echo htmlspecialchars($row['Mascota']); ?></p>
                    <p><strong>Usuario solicitante:</strong> <?php echo htmlspecialchars($row['Usuario']); ?></p>
                    <p><strong>Fecha de Solicitud:</strong> <?php echo htmlspecialchars($row['FechaAdopcion']->format('Y-m-d')); ?></p>
                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($row['EstadoAdopcion']); ?></p>
                    
                    <div class="acciones">
                        <form method="post" action="aprobar_adopcion.php">
                            <input type="hidden" name="adopcionID" value="<?php echo $row['AdopcionID']; ?>">
                            <input type="submit" class="boton" value="✅ Aprobar">
                        </form>
                        
                        <form method="post" action="rechazar_adopcion.php">
                            <input type="hidden" name="adopcionID" value="<?php echo intval($row['AdopcionID']); ?>">
                            <input type="hidden" name="mascotaID" value="<?php echo intval($row['MascotaID']); ?>">
                            <button type="submit" class="rechazo">⛔ Rechazar</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        </div>

        <?php if (!$solicitudes_exist) { ?>
            <div class="empty-state">
                <svg style="width:24px;height:24px;fill:currentColor" viewBox="0 0 24 24">
                    <path d="M11 15h2v2h-2zm0-8h2v6h-2zm1-5C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
                <p>No hay solicitudes de adopción pendientes</p>
            </div>
        <?php } ?>

        <a href="index.php" class="back-link">← Volver al inicio</a>
    </div>
</body>
</html>
