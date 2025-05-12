<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../Logic/db.php';

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuarioID']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Verificar que se envió un ID de adopción válido
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['adopcionID'])) {
    $adopcionID = $_POST['adopcionID'];

    // **Actualizar estado de adopción a "aceptada"**
    $sql = "UPDATE Adopcion SET EstadoAdopcion = 'aceptada' WHERE AdopcionID = ?";
    $params = [$adopcionID];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        // **Actualizar estado de la mascota a "adoptado"**
        $sql_update_mascota = "UPDATE Mascota SET Estado = 'adoptado' WHERE MascotaID = (
            SELECT MascotaID FROM Adopcion WHERE AdopcionID = ?
        )";
        $params_mascota = [$adopcionID];
        sqlsrv_query($conn, $sql_update_mascota, $params_mascota);

        header("Location: dashboard.php?mensaje=aprobado");
    }
}
?>
