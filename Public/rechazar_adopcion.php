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

// **Verificar que se enviaron valores válidos**
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['adopcionID']) || !isset($_POST['mascotaID'])) {
        die("<p style='color:red;'>Error: No se recibieron los datos correctamente.</p>");
    }

    $adopcionID = intval($_POST['adopcionID']);
    $mascotaID  = intval($_POST['mascotaID']);

    if ($adopcionID <= 0 || $mascotaID <= 0) {
        die("<p style='color:red;'>Error: Datos inválidos recibidos.</p>");
    }

    // **Verificar si el ID de adopción existe en la base de datos**
    $sql_check = "SELECT * FROM Adopcion WHERE AdopcionID = ?";
    $params_check = [$adopcionID];
    $stmt_check = sqlsrv_query($conn, $sql_check, $params_check);

    if (!$stmt_check) {
        die("<p style='color:red;'>Error en la consulta de verificación: " . print_r(sqlsrv_errors(), true) . "</p>");
    }

    $row = sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC);

    if ($row) {
        // **Actualizar estado de adopción a "rechazada"**
        $sql = "UPDATE Adopcion SET EstadoAdopcion = 'rechazada' WHERE AdopcionID = ?";
        $params = [$adopcionID];
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            // **Liberar la mascota para futuras adopciones**
            $sql_update_mascota = "UPDATE Mascota SET Estado = 'disponible' WHERE MascotaID = ?";
            $params_mascota = [$mascotaID];
            $stmt_mascota = sqlsrv_query($conn, $sql_update_mascota, $params_mascota);
            // **Eliminar la adopción rechazada para permitir nuevas solicitudes**
            $sql_eliminar = "DELETE FROM Adopcion WHERE AdopcionID = ?";
            $params_eliminar = [$adopcionID];
            $stmt_eliminar = sqlsrv_query($conn, $sql_eliminar, $params_eliminar);
            if ($stmt) {
                // **Actualizar estado de la mascota a "disponible"**
                $sql_update_mascota = "UPDATE Mascota SET Estado = 'disponible' WHERE MascotaID = ?";
                $params_mascota = [$mascotaID];
                sqlsrv_query($conn, $sql_update_mascota, $params_mascota);
            }



            if (!$stmt_eliminar) {
                die("<p style='color:red;'>Error al eliminar la adopción rechazada: " . print_r(sqlsrv_errors(), true) . "</p>");
            }


            if (!$stmt_mascota) {
                die("<p style='color:red;'>Error al actualizar el estado de la mascota: " . print_r(sqlsrv_errors(), true) . "</p>");
            }

            if ($stmt_mascota) {
                header("Location: dashboard.php?mensaje=rechazado");
                exit;
            } else {
                die("<p style='color:red;'>Error al actualizar el estado de la mascota.</p>");
            }
        } else {
            die("<p style='color:red;'>Error al rechazar la adopción.</p>");
        }
    } else {
        die("<p style='color:red;'>Error: La adopción no existe en la base de datos.</p>");
    }
}
