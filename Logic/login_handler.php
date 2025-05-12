<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once dirname(__FILE__) . '/../Logic/db.php';
include_once dirname(__FILE__) . '/../Logic/auth.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST['correo']); // Evita problemas con espacios en blanco
    $clave  = $_POST['clave'];

    // Verificar credenciales
    $usuario = verificarCredenciales($conn, $correo, $clave);

    // Depuración: Verifica si `verificarCredenciales()` devuelve datos correctamente
    if (!$usuario) {
        header("Location: ../Public/login.php?error=1");
        exit;
    }

    // Almacenar sesión correctamente
    $_SESSION['usuario']   = $usuario;
    $_SESSION['usuarioID'] = $usuario['UsuarioID'];
    $_SESSION['nombre']    = $usuario['Nombre'];
    $_SESSION['tipo']      = $_SESSION['tipo']; // **Usa el valor ya asignado en `auth.php`**

    // **Depuración: Imprime valores antes de la redirección**
    // echo "<pre>";
    // print_r($_SESSION);
    // echo "</pre>";
    // exit;

    // Redirigir según el tipo de usuario
    if ($_SESSION['tipo'] === 'admin') {
        header("Location: ../Public/index.php");
    } else {
        header("Location: ../Public/index.php");
    }
    exit;
}
?>
