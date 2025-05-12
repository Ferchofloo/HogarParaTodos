<?php
include_once '../Logic/db.php';
session_start();
// header("Cache-Control: no-cache, no-store, must-revalidate");
// header("Pragma: no-cache");
// header("Expires: 0");

// if(!isset($_SESSION['usuario'])){
//     header("Location: login.php?mensaje=error");
//     exit;
// }

$sql = "SELECT MascotaID, Nombre, Especie, Raza, Edad, Descripcion, FotoURL, Estado, FechaSubida 
        FROM Mascota 
        WHERE Estado != 'adoptado'
        ORDER BY FechaSubida DESC";
$stmt = sqlsrv_query($conn, $sql);

if (!$stmt) {
    echo "<p style='color:red;'>Error al obtener la lista de mascotas:</p>";
    print_r(sqlsrv_errors());
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lista de Mascotas</title>
    <style>
        :root {
            --primary: #2E7D32;
            --secondary: #FFAB40;
            --accent: #8D6E63;
            --light: #FFF8F0;
            --text: #333;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            padding: 2rem;
            background: var(--light);
            color: var(--text);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 2rem;
        }

        .pets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 1rem;
        }

        .pet-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .pet-card:hover {
            transform: translateY(-5px);
        }

        .pet-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-bottom: 3px solid var(--secondary);
        }

        .pet-info {
            padding: 1.5rem;
        }

        .pet-info h2 {
            margin: 0 0 1rem 0;
            color: var(--primary);
        }

        .pet-detail {
            margin: 0.5rem 0;
            font-size: 0.95rem;
        }

        .status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        .disponible {
            background: #C8E6C9;
            color: #2E7D32;
        }

        .en-proceso {
            background: #FFF3E0;
            color: #EF6C00;
        }

        .adoption-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            margin-top: 1rem;
            background: var(--secondary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .adoption-btn:hover {
            background: #FF8F00;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 2rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .pets-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐾 Mascotas en Adopción</h1>
        
        <div class="pets-grid">
            <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                <div class="pet-card">
                    <?php if (!empty($row['FotoURL'])) { ?>
                        <img src="<?php echo htmlspecialchars($row['FotoURL']); ?>" 
                             alt="Foto de <?php echo htmlspecialchars($row['Nombre']); ?>" 
                             class="pet-image">
                    <?php } ?>
                    
                    <div class="pet-info">
                        <h2><?php echo htmlspecialchars($row['Nombre']); ?></h2>
                        
                        <p class="pet-detail"><strong>Especie:</strong> <?php echo htmlspecialchars($row['Especie']); ?></p>
                        <p class="pet-detail"><strong>Raza:</strong> <?php echo htmlspecialchars($row['Raza']); ?></p>
                        <p class="pet-detail"><strong>Edad:</strong> <?php echo htmlspecialchars($row['Edad']); ?></p>
                        <p class="pet-detail"><strong>Descripción:</strong> <?php echo htmlspecialchars($row['Descripcion']); ?></p>
                        
                        <div class="status <?php echo str_replace(' ', '-', strtolower($row['Estado'])); ?>">
                            <?php echo htmlspecialchars($row['Estado']); ?>
                        </div>
                        
                        <!-- Se elimina el botón de solicitar adopción y editar.
                             Se muestra solo el botón para ver detalles. -->
                        <a href="ver_mascota.php?mascotaID=<?php echo $row['MascotaID']; ?>" class="adoption-btn" style="background: var(--primary); margin-top: 0.5rem;">
                            📖 Ver Detalles
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>

        <a href="index.php" class="back-link">← Volver al inicio</a>
    </div>
</body>
</html>
