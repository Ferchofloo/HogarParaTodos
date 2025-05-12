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

if (!isset($_GET['mascotaID'])) {
    echo "<p style='color:red;'>No se ha especificado la mascota.</p>";
    exit;
}

$mascotaID = $_GET['mascotaID'];
$usuarioID = $_SESSION['usuarioID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comentarios = isset($_POST['comentarios']) ? $_POST['comentarios'] : null;

    // **Verificar si la mascota tuvo una adopción rechazada**
    $sql_verificar = "SELECT AdopcionID, EstadoAdopcion FROM Adopcion WHERE MascotaID = ?";
    $params_verificar = [$mascotaID];
    $stmt_verificar = sqlsrv_query($conn, $sql_verificar, $params_verificar);

    if ($row = sqlsrv_fetch_array($stmt_verificar, SQLSRV_FETCH_ASSOC)) {
        if ($row['EstadoAdopcion'] === 'rechazada') {
            // **Eliminar la adopción rechazada para permitir nuevas solicitudes**
            $sql_eliminar = "DELETE FROM Adopcion WHERE AdopcionID = ?";
            $params_eliminar = [$row['AdopcionID']];
            sqlsrv_query($conn, $sql_eliminar, $params_eliminar);
        } elseif (in_array($row['EstadoAdopcion'], ['pendiente', 'aceptada'])) {
            echo "<div style='
    max-width: 600px;
    margin: 2rem auto;
    padding: 1.5rem;
    border-radius: 8px;
    background: #FFEBEE;
    border-left: 5px solid #D32F2F;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    '>
    
    <div style='
        display: flex;
        align-items: center;
        gap: 0.8rem;
        justify-content: center;
        margin-bottom: 1rem;
    '>
        <svg style='width:24px;height:24px;fill:#D32F2F' viewBox='0 0 24 24'>
            <path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'/>
        </svg>
        <p style='
            color: #D32F2F;
            margin: 0;
            font-size: 1.2rem;
            font-weight: 500;
        '>Esta mascota ya tiene una solicitud activa y no puede ser adoptada nuevamente.</p>
    </div>
    
    <div style='
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #FFF3E0;
        padding: 0.8rem 1.5rem;
        border-radius: 25px;
    '>
        <div style='
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #FFB74D;
            animation: pulse 1.2s infinite;
        '></div>
        <p style='
            margin: 0;
            color: #EF6C00;
            font-size: 0.9rem;
        '>Serás redirigido al inicio en 5 segundos...</p>
    </div>
</div>

<style>
    @keyframes pulse {
        0% { transform: scale(0.8); opacity: 0.8; }
        50% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(0.8); opacity: 0.8; }
    }
</style>";
        ?>
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';  
            }, 5000);
        </script>
        <?php
            exit;
        }
    }

    // **Insertar nueva solicitud**
    $sql_insert = "INSERT INTO Adopcion (MascotaID, UsuarioID, FechaAdopcion, EstadoAdopcion, Comentarios)
               VALUES (?, ?, GETDATE(), 'pendiente', ?)";
    $params_insert = [$mascotaID, $usuarioID, $comentarios];
    $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);

    if ($stmt_insert) {
        // **Actualizar estado de la mascota a "pendiente"**
        $sql_update_mascota = "UPDATE Mascota SET Estado = 'pendiente' WHERE MascotaID = ?";
        $params_mascota = [$mascotaID];
        sqlsrv_query($conn, $sql_update_mascota, $params_mascota);
    }

    if (!$stmt_insert) {
        echo "<div style='
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background: #FFEBEE;
    border-radius: 8px;
    border-left: 5px solid #D32F2F;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    '>
    
    <div style='
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    '>
        <svg style='min-width:28px; height:28px; fill:#D32F2F' viewBox='0 0 24 24'>
            <path d='M11 15h2v2h-2zm0-8h2v6h-2zm1-5C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z'/>
        </svg>
        
        <div>
            <p style='
                color: #D32F2F;
                margin: 0 0 0.5rem 0;
                font-size: 1.3rem;
                font-weight: 600;
            '>⚠️ Error en la solicitud</p>
            
            <div style='
                background: #FFFFFF90;
                padding: 1rem;
                border-radius: 4px;
                font-family: monospace;
                font-size: 0.9rem;
                color: #B71C1C;
            '>
                " . print_r(sqlsrv_errors(), true) . "
            </div>
        </div>
    </div>

    <div style='
        display: flex;
        align-items: center;
        gap: 0.8rem;
        background: #FFF3E0;
        padding: 1rem;
        border-radius: 6px;
        border: 1px solid #FFCC80;
    '>
        <div style='
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #FFA726;
            animation: pulse 1.2s infinite;
        '></div>
        <p style='
            margin: 0;
            color: #EF6C00;
            font-size: 0.95rem;
        '>Serás redirigido al inicio en 5 segundos...</p>
    </div>
</div>

<style>
    @keyframes pulse {
        0% { transform: scale(0.9); opacity: 0.8; }
        50% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(0.9); opacity: 0.8; }
    }
</style>";
        ?>
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';  
            }, 5000);
        </script>
        <?php
    } else {
        // Mensaje de éxito y timer de 5 segundos para redirigir
        echo "<div style='
    max-width: 600px;
    margin: 2rem auto;
    padding: 2rem;
    background: #E8F5E9;
    border-left: 5px solid #2E7D32;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    '>
    
    <div style='
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    '>
        <svg style='min-width:32px; height:32px; fill:#2E7D32' viewBox='0 0 24 24'>
            <path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z'/>
        </svg>
        
        <div>
            <p style='
                color: #2E7D32;
                margin: 0;
                font-size: 1.3rem;
                font-weight: 600;
            '>✅ Solicitud realizada con éxito</p>
            <p style='
                color: #1B5E20;
                margin: 0.5rem 0 0 0;
                font-size: 1rem;
            '>Espera la respuesta del administrador</p>
        </div>
    </div>

    <div style='
        display: flex;
        align-items: center;
        gap: 0.8rem;
        background: #F1F8E9;
        padding: 1rem;
        border-radius: 6px;
    '>
        <div style='
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #66BB6A;
            animation: pulse 1.2s infinite;
        '></div>
        <p style='
            margin: 0;
            color: #2E7D32;
            font-size: 0.95rem;
        '>Serás redirigido al inicio en 5 segundos...</p>
    </div>
</div>

<style>
    @keyframes pulse {
        0% { transform: scale(0.9); opacity: 0.8; }
        50% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(0.9); opacity: 0.8; }
    }
</style>";
        ?>
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';  
            }, 5000);
        </script>
        <?php
    }
} else {
?>
    <!DOCTYPE html>
<html>
<head>
    <title>Solicitud de Adopción</title>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: var(--light);
            padding: 2rem;
        }

        .form-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
            margin-top: 2rem;
        }

        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.2rem;
        }

        .adoption-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        label {
            font-weight: 500;
            color: var(--primary);
            font-size: 1.1rem;
        }

        textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            outline: none;
            border-color: var(--secondary);
        }

        .submit-btn {
            background: var(--secondary);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.3s ease, background 0.3s ease;
            align-self: center;
        }

        .submit-btn:hover {
            background: #FF8F00;
            transform: translateY(-2px);
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--accent);
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 1.5rem;
                width: 100%;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>🐾 Solicitar Adopción</h1>
        
        <form method="post" class="adoption-form">
            <label>Comentarios adicionales (opcional):</label>
            <textarea 
                name="comentarios"
                placeholder="Cuéntanos algo sobre ti y por qué quieres adoptar..."
            ></textarea>
            
            <button type="submit" class="submit-btn">
                📨 Enviar Solicitud
            </button>
        </form>
        
        <a href="lista_mascotas.php" class="back-link">← Volver a la Lista de Mascotas</a>
    </div>
</body>
</html>
<?php
}
?>
