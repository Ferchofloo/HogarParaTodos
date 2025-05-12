<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Función para verificar las credenciales del usuario.
 * Revisa tanto si el usuario es administrador como si es normal.
 */
function verificarCredenciales($conn, $correo, $clave) {
    // Verificar si el usuario es administrador
    $sql_admin = "SELECT AdminID AS UsuarioID, Nombre, Email, Contrasena FROM Administrador WHERE Email = ?";
    $params = [$correo];
    $stmt_admin = sqlsrv_query($conn, $sql_admin, $params);

    if (!$stmt_admin) {
        die("Error al consultar Administrador: " . print_r(sqlsrv_errors(), true));
    }

    if ($row = sqlsrv_fetch_array($stmt_admin, SQLSRV_FETCH_ASSOC)) {
        if (password_verify($clave, $row['Contrasena'])) {
            $_SESSION['usuario']   = $row;
            $_SESSION['usuarioID'] = $row['UsuarioID'];
            $_SESSION['nombre']    = $row['Nombre'];
            $_SESSION['tipo']      = 'admin'; // Asignar "admin"
            return $row; // Login correcto
        } else {
            return false; // Contraseña incorrecta
        }
    }

    // Verificar si el usuario es normal
    $sql_user = "SELECT UsuarioID, Nombre, Email, Contrasena FROM Usuario WHERE Email = ?";
    $stmt_user = sqlsrv_query($conn, $sql_user, $params);

    if (!$stmt_user) {
        die("Error al consultar Usuario: " . print_r(sqlsrv_errors(), true));
    }

    if ($row = sqlsrv_fetch_array($stmt_user, SQLSRV_FETCH_ASSOC)) {
        // Validar contraseña antes de permitir acceso
        if (password_verify($clave, $row['Contrasena'])) {
            $_SESSION['usuario']   = $row;
            $_SESSION['usuarioID'] = $row['UsuarioID'];
            $_SESSION['nombre']    = $row['Nombre'];
            $_SESSION['tipo']      = 'normal'; // Asignar "normal"
            return $row; // Login correcto
        } else {
            return false; // Contraseña incorrecta
        }
    }

    return false; // Usuario no encontrado
}

/**
 * Función para registrar un nuevo usuario.
 * Valida que el email no se encuentre registrado y, en caso contrario,
 * inserta el usuario incluyendo los campos DUI y Teléfono.
 */
function registrarUsuario($conn, $nombre, $correo, $clave, $dui, $telefono) {
    // Verificar que el email no esté registrado
    $sql_check = "SELECT UsuarioID FROM Usuario WHERE Email = ?";
    $params_check = [$correo];
    $stmt_check = sqlsrv_query($conn, $sql_check, $params_check);
    if ($stmt_check === false) {
        die("Error al validar el usuario: " . print_r(sqlsrv_errors(), true));
    }
    if (sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC)) {
        // Retornamos false si el email ya existe
        return false;
    }
    
    // Hashear la contraseña
    $claveHash = password_hash($clave, PASSWORD_DEFAULT);
    
    // Insertar el nuevo usuario incluyendo DUI y Teléfono
    $sql_insert = "INSERT INTO Usuario (Nombre, Email, Contrasena, FechaRegistro, DUI, Telefono) VALUES (?, ?, ?, GETDATE(), ?, ?)";
    $params_insert = [$nombre, $correo, $claveHash, $dui, $telefono];
    $stmt_insert = sqlsrv_query($conn, $sql_insert, $params_insert);
    
    if ($stmt_insert === false) {
        die("Error al registrar usuario: " . print_r(sqlsrv_errors(), true));
    }
    
    return true;
}
?>
