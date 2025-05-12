<?php
session_start(); 
// Limpiar todas las variables de sesión
$_SESSION = array();

// Si se desea destruir una cookie de sesión, se debe hacer también.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión.
session_destroy();

// Redirigir al login con un mensaje (por ejemplo, parámetro "mensaje")
header("Location: login.php?mensaje=logout");
exit;
?>
