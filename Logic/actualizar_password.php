<?php
include_once 'db.php';

$emailAdmin = 'admin@example1.com'; // **Cambia esto por el email correcto del administrador**
$nuevaContrasena = 'admin123'; // **Nueva contraseña**

$hashedPassword = password_hash($nuevaContrasena, PASSWORD_DEFAULT);

$sql = "UPDATE Administrador SET Contrasena = ? WHERE Email = ?";
$params = [$hashedPassword, $emailAdmin];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo "<p style='color:green;'>Contraseña actualizada correctamente.</p>";
} else {
    echo "<p style='color:red;'>Error al actualizar la contraseña: " . print_r(sqlsrv_errors(), true) . "</p>";
}
?>
