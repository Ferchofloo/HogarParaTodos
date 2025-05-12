<?php
session_start();
$loggedIn = isset($_SESSION['usuario']);
$tipoUsuario = $_SESSION['tipo'] ?? 'usuario'; // Define el tipo de usuario
include_once '../Logic/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HogarParaTodos - Inicio</title>
  <style>
    :root {
      --primary: #2E7D32;
      --secondary: #FFAB40;
      --accent: #8D6E63;
      --light: #FFF8F0;
    }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      margin: 0;
      padding: 0;
      background: var(--light);
      line-height: 1.6;
    }

    header {
      background: linear-gradient(15deg, var(--primary), #1B5E20);
      padding: 1.5rem;
      color: white;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    header h1 {
      margin: 0;
      font-size: 2.2rem;
      letter-spacing: -1px;
    }

    .container {
      padding: 2rem 1.5rem;
      max-width: 800px;
      margin: 2rem auto;
      background: white;
      border-radius: 12px;
      box-shadow: 0 3px 15px rgba(0,0,0,0.08);
    }

    .features-list {
      list-style: none;
      padding: 0;
    }

    .features-list li {
      padding: 1rem;
      margin: 1rem 0;
      background: #f8f8f8;
      border-left: 4px solid var(--secondary);
      border-radius: 4px;
      transition: transform 0.2s ease;
    }

    .features-list li:hover {
      transform: translateX(5px);
    }

    footer {
      background: var(--primary);
      color: white;
      text-align: center;
      padding: 1.5rem;
      margin-top: 3rem;
    }

    .auth-buttons {
      margin-top: 2rem;
      display: flex;
      gap: 1rem;
      justify-content: center;
    }

    .auth-buttons a {
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .login-btn {
      background: var(--secondary);
      color: white;
    }

    .register-btn {
      background: var(--accent);
      color: white;
    }

    @media (max-width: 768px) {
      .container {
        margin: 1rem;
        padding: 1.5rem;
      }
      
      header h1 {
        font-size: 1.8rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>🐾 HogarParaTodos</h1>
  </header>

  <?php
  if ($loggedIn) {
      if ($tipoUsuario === 'admin') {
          include 'includes/nav_admin.php';
      } else {
          include 'includes/nav_usuario.php';
      }
  } else {
      include 'includes/nav_publico.php';
  }
  ?>

  <div class="container">
    <?php if ($loggedIn && $tipoUsuario === 'admin') { ?>
         <!-- Contenido exclusivo para administradores -->
         <h2>Bienvenido, Administrador</h2>
         <p class="lead">Este es tu panel de administración de <strong>HogarParaTodos</strong>.</p>
         <ul class="features-list">
             <li>
                 <strong>📊 Estadísticas:</strong>
                 <span>Accede a informes y datos sobre adopciones, visitas y actividades de la plataforma.</span>
             </li>
             <li>
                 <strong>🛠 Gestión de Solicitudes:</strong>
                 <span>Revisa, aprueba o rechaza las solicitudes de adopción recibidas.</span>
             </li>
             <li>
                 <strong>🐾 Gestión de Mascotas:</strong>
                 <span>Administra el catálogo de mascotas, actualiza sus estados y verifica la información.</span>
             </li>
         </ul>
         <p>Desde este panel, puedes supervisar todos los aspectos relacionados con el funcionamiento del centro de adopción y garantizar que cada mascota reciba la atención que merece.</p>
    <?php } else { ?>
         <!-- Contenido para usuarios y visitantes -->
         <h2>Encuentra a tu compañero ideal</h2>
         <p class="lead">Conectamos corazones humanos con mascotas que necesitan un hogar.</p>
         
         <ul class="features-list">
             <li>
                 <strong>🐶 Mascotas Disponibles:</strong>
                 <span>Descubre a nuestros amigos esperando una familia</span>
                 <!-- Mostrar las 2 mascotas más recientes disponibles -->
                 <ul class="recent-mascotas" style="margin-top:10px; list-style: none; padding: 0;">
                   <?php
                   // Consulta para obtener 2 mascotas disponibles ordenadas por fecha de subida descendente
                   $sql_available = "SELECT TOP 2 Nombre, Especie, FotoURL FROM Mascota WHERE Estado = 'disponible' ORDER BY FechaSubida DESC";
                   $stmt_available = sqlsrv_query($conn, $sql_available);
                   while ($mascota = sqlsrv_fetch_array($stmt_available, SQLSRV_FETCH_ASSOC)) {
                   ?>
                     <li style="display: flex; align-items: center; margin: 5px 0;">
                       <img src="<?php echo htmlspecialchars($mascota['FotoURL']); ?>" alt="<?php echo htmlspecialchars($mascota['Nombre']); ?>" style="width:50px; height:50px; object-fit:cover; border-radius:50%; margin-right:10px;">
                       <span><?php echo htmlspecialchars($mascota['Nombre']); ?> (<?php echo htmlspecialchars($mascota['Especie']); ?>)</span>
                     </li>
                   <?php } ?>
                 </ul>
             </li>
             <?php if (!$loggedIn) { ?>
                 <li>
                     <strong>🔑 Acceso a la Comunidad:</strong>
                     <span>Únete para gestionar tus solicitudes de adopción</span>
                     <div class="auth-buttons">
                         <a href="login.php" class="login-btn">Iniciar Sesión</a>
                         <a href="register.php" class="register-btn">Registrarse</a>
                     </div>
                 </li>
             <?php } else if ($tipoUsuario === 'usuario') { ?>
                 <li>
                     <strong>❤️ Solicitar Adopción:</strong>
                     <span>Inicia el proceso para adoptar</span>
                 </li>
                 <li>
                     <strong>📋 Mi Perfil:</strong>
                     <span>Administra tu información y solicitudes</span>
                 </li>
             <?php } ?>
         </ul>
         <!-- Información adicional para usuarios/visitantes -->
         <p>En <strong>HogarParaTodos</strong> trabajamos incansablemente para unir a mascotas necesitadas con familias que les puedan brindar un hogar lleno de amor y cuidado. Nuestro centro de adopción se dedica a garantizar que cada mascota reciba la atención y el cariño que merece.</p>
         <p>Contamos con un equipo profesional que evalúa el estado de salud y comportamiento de cada animal, asegurando que se encuentren en óptimas condiciones para la adopción. Además, ofrecemos asesoría personalizada durante todo el proceso, garantizando una transición exitosa para la mascota y la familia adoptante.</p>
         <p>Únete a nuestra comunidad y descubre la felicidad de brindar una segunda oportunidad a un ser que espera ser amado. En <strong>HogarParaTodos</strong> creemos que cada vida cuenta y que, juntos, podemos construir un mundo con más empatía y compromiso hacia los animales.</p>
    <?php } ?>
  </div>

  <footer>
    <p>&copy; 2025 HogarParaTodos. Todos los derechos reservados.</p>
  </footer>
</body>
</html>
