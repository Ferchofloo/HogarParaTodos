<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if(!isset($_SESSION['usuario'])){
    header("Location: login.php?mensaje=error");
    exit;
}

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuarioID'])) {
    echo "Error: No tienes una sesión activa. Redirigiendo al login...";
    header("Refresh: 2; URL=login.php"); // Mensaje y redirección tras 2 segundos
    exit;
}

include_once '../Logic/db.php'; // Incluir archivo de conexión a la base de datos

$usuarioID = $_SESSION['usuarioID']; // Obtener el usuario autenticado

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre      = $_POST['nombre'];
    $especie     = $_POST['especie'];
    $raza        = !empty($_POST['raza']) ? $_POST['raza'] : null;
    $edad        = !empty($_POST['edad']) ? $_POST['edad'] : null;
    $descripcion = !empty($_POST['descripcion']) ? $_POST['descripcion'] : null;
    
    // Procesar el archivo de imagen
    if (isset($_FILES['fotoURL']) && $_FILES['fotoURL']['error'] == 0) {
        // Obtener la extensión del archivo en minúsculas
        $extension = strtolower(pathinfo($_FILES['fotoURL']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'png', 'giff'];
        if (!in_array($extension, $allowed_ext)) {
            echo "<p style='color:red;'>Error: El formato de la imagen debe ser JPG, PNG o GIFF.</p>";
            exit;
        }
        
        // Definir la carpeta destino (ajusta la ruta según tu estructura)
        $destination_dir = "../Public/Img/uploads/";
        if (!is_dir($destination_dir)) {
            mkdir($destination_dir, 0777, true);
        }
        
        // Generar un nombre único para el archivo
        $new_filename = uniqid("img_", true) . "." . $extension;
        $destination_path = $destination_dir . $new_filename;
        
        // Mover el archivo temporal a la carpeta destino
        if (move_uploaded_file($_FILES['fotoURL']['tmp_name'], $destination_path)) {
            // Definir la URL que se guardará en la base de datos (ruta relativa)
            $fotoURL = "Img/uploads/" . $new_filename;
        } else {
            echo "<p style='color:red;'>Error: No se pudo subir la imagen.</p>";
            exit;
        }
    } else {
        // Si no se sube archivo, se puede definir como nulo o manejarlo como error
        echo "<p style='color:red;'>Error: Debe subir una imagen.</p>";
        exit;
    }
    
    // Preparar la consulta con el procedimiento almacenado
    $sql = "EXEC sp_CrearMascota ?, ?, ?, ?, ?, ?, ?";
    $params = [$nombre, $especie, $usuarioID, $raza, $edad, $descripcion, $fotoURL];
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if (!$stmt) {
        echo "<p style='color: red;'>Error al crear mascota:</p>";
        print_r(sqlsrv_errors());
    } else {
        echo "<p style='color: green;'>Mascota creada exitosamente.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrar Mascota</title>
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

        .registration-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            max-width: 600px;
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

        .pet-form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        label {
            color: var(--primary);
            font-weight: 500;
            font-size: 1rem;
        }

        input, textarea, .file-input {
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(255,171,64,0.2);
        }

        .file-input {
            position: relative;
            background: #f8f8f8;
            border: 2px dashed #ddd;
            text-align: center;
            cursor: pointer;
        }

        .file-input input[type="file"] {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input:hover {
            border-color: var(--secondary);
        }

        .submit-btn {
            background: var(--primary);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            background: #1B5E20;
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
            .registration-container {
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <h1>🐾 Registrar Mascota</h1>
        
        <form method="post" enctype="multipart/form-data" class="pet-form">
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" required>
            </div>

            <div class="form-group">
                <label>Especie:</label>
                <input type="text" name="especie" required>
            </div>

            <div class="form-group">
                <label>Raza:</label>
                <input type="text" name="raza">
            </div>

            <div class="form-group">
                <label>Edad:</label>
                <input type="number" name="edad" min="0" max="100">
            </div>

            <div class="form-group">
                <label>Descripción:</label>
                <textarea name="descripcion"></textarea>
            </div>

            <div class="form-group">
                <label>Subir imagen:</label>
                <div class="file-input">
                    <span>📤 Haz clic para subir imagen</span>
                    <input type="file" name="fotoURL" required>
                </div>
                <small style="color: #666;">Formatos permitidos: JPG, PNG, GIF</small>
            </div>

            <button type="submit" class="submit-btn">🐶 Registrar Mascota</button>
        </form>

        <a href="index.php" class="back-link">← Volver al inicio</a>
    </div>

    <!-- Validaciones del formulario (mejoradas visualmente) -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            let errors = [];
            const edadField = document.querySelector('input[name="edad"]');
            const fileInput = document.querySelector('input[name="fotoURL"]');
            
            if (edadField.value) {
                const edad = parseInt(edadField.value, 10);
                if (isNaN(edad) || edad < 0 || edad > 100) {
                    errors.push("La edad debe ser un número entre 0 y 100");
                    edadField.style.borderColor = '#D32F2F';
                } else {
                    edadField.style.borderColor = '#ddd';
                }
            }

            if(fileInput.value) {
                const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
                if(!allowedExtensions.exec(fileInput.value)) {
                    errors.push("El formato de la imagen debe ser JPG, PNG o GIF");
                    fileInput.parentElement.style.borderColor = '#D32F2F';
                } else {
                    fileInput.parentElement.style.borderColor = '#ddd';
                }
            }

            if(errors.length > 0) {
                e.preventDefault();
                alert("Errores:\n\n" + errors.join("\n"));
                return false;
            }
        });
    </script>
</body>
</html>
