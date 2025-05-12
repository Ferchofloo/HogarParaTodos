<?php
include 'db.php';
include 'auth.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Captura de los campos existentes
    $nombre    = trim($_POST['nombre']);
    $correo    = trim($_POST['correo']);
    $clave     = trim($_POST['clave']);
    
    // Nuevos campos añadidos
    $dui       = trim($_POST['dui']);      // Se espera el formato XXXXXXXX-X
    $telefono  = trim($_POST['telefono']);

    // Llamada a la función para registrar usuario
    if (registrarUsuario($conn, $nombre, $correo, $clave, $dui, $telefono)) {
        header("Location: ../public/login.php?registro=exito");
        exit; // Se recomienda detener la ejecución luego de redireccionar
    } else {
        echo "Error al registrar usuario:<br>";
        die(print_r(sqlsrv_errors(), true));
    }
}
?>
