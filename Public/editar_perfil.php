<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

include_once '../Logic/db.php';

$usuarioID = $_SESSION['usuarioID'];

// **Consultar información actual**
$sql_usuario = "SELECT Nombre, Email, Telefono, Direccion FROM Usuario WHERE UsuarioID = ?";
$params_usuario = [$usuarioID];
$stmt_usuario = sqlsrv_query($conn, $sql_usuario, $params_usuario);
$usuario = sqlsrv_fetch_array($stmt_usuario, SQLSRV_FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];

    // **Actualizar información del usuario**
    $sql_update = "UPDATE Usuario SET Nombre = ?, Email = ?, Telefono = ?, Direccion = ? WHERE UsuarioID = ?";
    $params_update = [$nombre, $email, $telefono, $direccion, $usuarioID];
    $stmt_update = sqlsrv_query($conn, $sql_update, $params_update);

    if ($stmt_update) {
        header("Location: perfil_usuario.php?mensaje=actualizado");
        exit;
    } else {
        echo "<p style='color:red;'>Error al actualizar perfil: " . print_r(sqlsrv_errors(), true) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
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

        .edit-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }

        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.2rem;
            border-bottom: 3px solid var(--secondary);
            padding-bottom: 0.5rem;
        }

        .profile-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        label {
            color: var(--primary);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        label::before {
            content: "•";
            color: var(--secondary);
            font-size: 1.4rem;
        }

        input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(255,171,64,0.2);
        }

        button[type="submit"] {
            background: var(--secondary);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.3s ease, background 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        button[type="submit"]:hover {
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
            .edit-container {
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h1>✏️ Editar Perfil</h1>
        
        <form method="post" class="profile-form">
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['Nombre']); ?>" required>
            </div>

            <div class="form-group">
                <label>Correo:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['Email']); ?>" required>
            </div>

            <div class="form-group">
                <label>Teléfono:</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario['Telefono'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Dirección:</label>
                <input type="text" name="direccion" value="<?php echo htmlspecialchars($usuario['Direccion'] ?? ''); ?>">
            </div>

            <button type="submit">💾 Guardar Cambios</button>
        </form>

        <a href="perfil_usuario.php" class="back-link">← Volver al Perfil</a>
    </div>
</body>
</html>
