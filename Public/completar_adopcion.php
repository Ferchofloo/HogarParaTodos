<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}
include_once '../Logic/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $adopcionID = $_POST['adopcionID'];
    // Se asume que el ID del administrador se guarda en sesión al iniciar sesión
    $adminID = $_SESSION['AdminID'];
    $sql = "EXEC sp_CompletarAdopcion ?, ?";
    $params = [$adopcionID, $adminID];
    $stmt = sqlsrv_query($conn, $sql, $params);
    if (!$stmt) {
        echo "Error al completar la adopción: ";
        print_r(sqlsrv_errors());
    } else {
        echo "Adopción completada exitosamente.";
    }
}
?>
