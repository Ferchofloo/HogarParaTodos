<?php
session_start();
if(isset($_GET['mensaje']) && $_GET['mensaje'] === 'logout'){
    echo "<p style='color:red;'>Tu sesión ha sido cerrada. Debes iniciar sesión nuevamente.</p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registrarse</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #2F4F4F;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0;
    }
    
    .main-box {
      display: flex;
      background: #72654e;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      overflow: hidden;
      width: 900px;
      height: 500px;
    }
    
    .image-box {
      flex: 1;
      background-color: #dfdfad;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .image-box img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      padding: 20px;
    }
    
    .container {
      flex: 1;
      background-color: #a0b6bd;
      color: #FFFFE0;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
      padding: 40px;
    }
    
    form {
      width: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center; 
    }
    
    form h2 {
      text-align: center;
      margin-bottom: 25px;
      font-size: 24px;
      color: #fff;
    }
    
    input {
      width: 94%;
      padding: 8px 10px;
      margin: 6px auto;
      border-radius: 6px;
      border: none;
      font-size: 13px;
    }
    
    button {
      width: 98%;
      padding: 12px;
      background-color: #2F4F4F;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 15px;
      cursor: pointer;
      margin: 10px auto 0;
      transition: background-color 0.3s ease;
    }
    
    button:hover {
      background-color: #a86348;
    }
    
    .switch-link {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
      color: #fff;
    }
    
    .switch-link a {
      color: #2F4F4F;
      font-weight: bold;
      text-decoration: none;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="main-box">
    <div class="image-box" class="image-box">
      <a href="index.php">
        <img src="img/Logo-Ayudame a vivir.png" alt="Imagen decorativa" style="cursor:pointer;">
      </a>
    </div>
    <div class="container">
      <form id="register-form" method="POST" action="../logic/register_handler.php">
        <h2>Registrarse</h2>
        <input type="text" name="nombre" placeholder="Nombre completo" required>
        <input type="email" name="correo" placeholder="Correo electrónico" required>
        <input type="password" name="clave" placeholder="Contraseña" required>
        <!-- Campos nuevos -->
        <input type="text" id="dui" name="dui" maxlength="10" placeholder="XXXXXXXX-X" required>
        <input type="number" id="telefono" name="telefono" placeholder="Teléfono (8 dígitos)" required>
        <button type="submit">Crear cuenta</button>
        <div class="switch-link">
          ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts para formatear campos DUI y Teléfono -->
  <script>
    // Script para DUI: Inserta automáticamente un guion luego de 8 dígitos
    document.getElementById('dui').addEventListener('input', function () {
      let value = this.value.replace(/\D/g, ''); // Elimina cualquier carácter que no sea dígito

      if (value.length === 8) {
        value = value + '-';
      } else if (value.length > 8) {
        value = value.slice(0, 8) + '-' + value.slice(8, 9);
      }
      this.value = value;
    });

    // Script para Teléfono: Permite solo números y limita a 8 dígitos
    document.getElementById('telefono').addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');
      if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
      }
    });
  </script>
</body>
</html>
