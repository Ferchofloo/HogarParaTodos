<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if(!isset($_SESSION['usuario'])){
    header("Location: login.php?mensaje=error");
    exit;
}

// Asegurarse que el usuario esté logueado y sea administrador
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo'] ?? 'usuario') !== 'admin') {
    header("Location: ../Public/login.php");
    exit;
}

include_once '../Logic/db.php'; // Asegúrate de que este archivo defina la variable $conn

// Verificar que se haya recibido el ID de la mascota a editar
if (!isset($_GET['mascotaID'])) {
    echo "<p>Error: No se especificó la mascota a editar.</p>";
    exit;
}

$mascotaID = $_GET['mascotaID'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre      = $_POST['nombre'];
    $especie     = $_POST['especie'];
    $raza        = !empty($_POST['raza']) ? $_POST['raza'] : null;
    $edad        = !empty($_POST['edad']) ? $_POST['edad'] : null;
    $descripcion = !empty($_POST['descripcion']) ? $_POST['descripcion'] : null;

    // Procesar nueva imagen (opcional)
    $fotoURL = null;
    if (isset($_FILES['fotoURL']) && $_FILES['fotoURL']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['fotoURL']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'png', 'giff'];
        if (!in_array($extension, $allowed_ext)) {
            echo "<p style='color:red;'>Error: El formato de la imagen debe ser JPG, PNG o GIFF.</p>";
            exit;
        }

        // Define la carpeta de destino (ajusta la ruta según la estructura de tu proyecto)
        $destination_dir = "../Public/Img/uploads/";
        if (!is_dir($destination_dir)) {
            mkdir($destination_dir, 0777, true);
        }

        // Generar un nombre único para la imagen para evitar colisiones
        $new_filename = uniqid("img_", true) . "." . $extension;
        $destination_path = $destination_dir . $new_filename;

        if (!move_uploaded_file($_FILES['fotoURL']['tmp_name'], $destination_path)) {
            echo "<p style='color:red;'>Error: No se pudo subir la imagen.</p>";
            exit;
        }

        // Se almacena la ruta relativa para usar en la base de datos
        $fotoURL = "Img/uploads/" . $new_filename;
    }

    // Actualizar la información de la mascota en la base de datos.
    // Se actualiza la imagen solo si se subió una nueva.
    if ($fotoURL !== null) {
        $sql = "UPDATE Mascota SET Nombre = ?, Especie = ?, Raza = ?, Edad = ?, Descripcion = ?, FotoURL = ? WHERE MascotaID = ?";
        $params = [$nombre, $especie, $raza, $edad, $descripcion, $fotoURL, $mascotaID];
    } else {
        $sql = "UPDATE Mascota SET Nombre = ?, Especie = ?, Raza = ?, Edad = ?, Descripcion = ? WHERE MascotaID = ?";
        $params = [$nombre, $especie, $raza, $edad, $descripcion, $mascotaID];
    }

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        echo "<div style='
    padding: 1.2rem;
    margin: 1rem 0;
    background: #FFEBEE;
    border-left: 4px solid #D32F2F;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    animation: slideIn 0.3s ease;
    '>
    
    <svg style='flex-shrink:0; width:24px; height:24px; fill:#D32F2F' viewBox='0 0 24 24'>
        <path d='M11 15h2v2h-2zm0-8h2v6h-2zm1-5C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z'/>
    </svg>
    
    <div>
        <p style='
            color: #D32F2F;
            margin: 0;
            font-weight: 500;
            font-size: 1.1rem;
        '>Error al actualizar la mascota:</p>
    </div>
</div>

<style>
    @keyframes slideIn {
        from { transform: translateX(20px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
</style>";
        print_r(sqlsrv_errors());
        exit;
    } else {
        // Muestra el mensaje de éxito y redirige al apartado de mascotas
        echo "<div style='
    padding: 1.5rem;
    margin: 2rem auto;
    max-width: 600px;
    background: #E8F5E9;
    border-left: 5px solid #2E7D32;
    border-radius: 8px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    animation: fadeIn 0.5s ease;
    '>
    
    <div style='
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    '>
        <svg style='width:32px; height:32px; fill:#2E7D32' viewBox='0 0 24 24'>
            <path d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/>
        </svg>
        
        <div>
            <p style='
                color: #2E7D32;
                margin: 0;
                font-size: 1.2rem;
                font-weight: 500;
            '>✅ Información actualizada exitosamente</p>
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
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #66BB6A;
            animation: pulse 1.2s infinite;
        '></div>
        <p style='
            margin: 0;
            color: #1B5E20;
            font-size: 0.95rem;
        '>Redirigiendo a la lista de mascotas...</p>
    </div>
</div>

<script>
    setTimeout(function(){ 
        window.location.href = 'lista_mascotas.php'; 
    }, 3000);
</script>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes pulse {
        0% { transform: scale(0.9); opacity: 0.8; }
        50% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(0.9); opacity: 0.8; }
    }
</style>";
        exit;
    }
} else {
    // Método GET: Cargar datos actuales de la mascota
    $sql = "SELECT * FROM Mascota WHERE MascotaID = ?";
    $params = [$mascotaID];
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        echo "<p style='color:red;'>Error al consultar la mascota:</p>";
        print_r(sqlsrv_errors());
        exit;
    }
    $mascota = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    if (!$mascota) {
        echo "<p style='color:red;'>Mascota no encontrada.</p>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Mascota - HogarParaTodos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --primary: #2E7D32;
      --secondary: #FFAB40;
      --accent: #8D6E63;
      --light: #FFF8F0;
    }
    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: var(--light);
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 600px;
      margin: 20px auto;
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    }
    h2 {
      text-align: center;
      color: var(--primary);
    }
    form {
      display: flex;
      flex-direction: column;
    }
    label {
      margin-top: 10px;
      font-weight: bold;
    }
    input[type="text"],
    input[type="number"],
    textarea {
      padding: 10px;
      margin-top: 5px;
      border: 1px solid var(--accent);
      border-radius: 6px;
    }
    input[type="file"] {
      margin-top: 5px;
    }
    input[type="submit"] {
      background: var(--primary);
      color: var(--light);
      padding: 12px;
      border: none;
      border-radius: 6px;
      margin-top: 20px;
      cursor: pointer;
      font-size: 16px;
    }
    input[type="submit"]:hover {
      background: var(--secondary);
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Editar Información de la Mascota</h2>
    <form method="post" enctype="multipart/form-data">
      <label>Nombre:</label>
      <input type="text" name="nombre" value="<?php echo htmlspecialchars($mascota['Nombre']); ?>" required>
      
      <label>Especie:</label>
      <input type="text" name="especie" value="<?php echo htmlspecialchars($mascota['Especie']); ?>" required>
      
      <label>Raza:</label>
      <input type="text" name="raza" value="<?php echo htmlspecialchars($mascota['Raza']); ?>">
      
      <label>Edad:</label>
      <input type="number" name="edad" value="<?php echo htmlspecialchars($mascota['Edad']); ?>">
      
      <label>Descripción:</label>
      <textarea name="descripcion"><?php echo htmlspecialchars($mascota['Descripcion']); ?></textarea>
      
      <label>Actualizar Imagen (opcional):</label>
      <input type="file" name="fotoURL">
      
      <input type="submit" value="Actualizar Mascota">
    </form>
  </div>
</body>
</html>
