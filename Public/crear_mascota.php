<?php
session_start();
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
</head>
<body>
    <h1>Registrar Mascota para Adopción</h1>
    <!-- Importante: Se agrega el atributo enctype="multipart/form-data" -->
    <form method="post" enctype="multipart/form-data">
        <label>Nombre:</label><br>
        <input type="text" name="nombre" required><br><br>

        <label>Especie:</label><br>
        <input type="text" name="especie" required><br><br>

        <label>Raza:</label><br>
        <input type="text" name="raza"><br><br>

        <label>Edad:</label><br>
        <input type="number" name="edad"><br><br>

        <label>Descripción:</label><br>
        <textarea name="descripcion"></textarea><br><br>

        <label>Subir imagen:</label><br>
        <input type="file" name="fotoURL" required><br><br>

        <input type="submit" value="Registrar Mascota">
    </form>
    <p><a href="index.php">Volver al index</a></p>
    
    <!-- Validaciones del formulario (opcional, del lado cliente) -->
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            var edadField = document.querySelector('input[name="edad"]');
            // Validar Edad: si se proporciona valor, debe ser entre 0 y 100
            if (edadField.value) {
                var edad = parseInt(edadField.value, 10);
                if (isNaN(edad) || edad < 0 || edad > 100) {
                    alert("Error: La edad debe ser un número entre 0 y 100.");
                    e.preventDefault(); // Evita el envío del formulario
                    return false;
                }
            }
            
            // Validar archivo de imagen (opcional, del lado cliente)
            var fileInput = document.querySelector('input[name="fotoURL"]');
            if(fileInput.value) {
                var allowedExtensions = /(\.jpg|\.png|\.giff)$/i;
                if(!allowedExtensions.exec(fileInput.value)) {
                    alert("Error: El formato de la imagen debe ser JPG, PNG o GIFF.");
                    e.preventDefault();
                    return false;
                }
            }
        });
    </script>
</body>
</html>
