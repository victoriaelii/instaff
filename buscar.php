<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    // El usuario no ha iniciado sesión, redirigir a index.html
    header("Location: index.html");
    exit();
}
?>

<?php
session_start();

require_once "data_access/db.php"; // Include the file containing getDbConnection() function

// buscar.php
if (isset($_GET['q'])) {
    $query = $_GET['q'];

    try {
        $pdo = getDbConnection(); // Get a PDO instance
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre LIKE ? OR username LIKE ?");
        $stmt->execute(["%$query%", "%$query%"]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle database connection errors
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de búsqueda</title>
    <link rel="stylesheet" href="css/buscar.css">
</head>
<body>
    <header>
        <h1>Resultados de la búsqueda</h1>
        <a href="index_sesion.php" class="button">Regresar</a>
    </header>
    <div class="resultados">
        <?php if (isset($resultados) && !empty($resultados)): ?>
            <div class="grid-container">
                <?php foreach ($resultados as $usuario): ?>
                    <div class="card">
                        <div class="content">
                            <h2><?php echo $usuario['nombre'] . ' ' . $usuario['apellidos']; ?></h2>
                            <?php if (isset($usuario['profesion'])): ?>
                                <p><?php echo $usuario['profesion']; ?></p>
                            <?php endif; ?>
                            <div class="center">
                                <?php if (isset($usuario['followers'])): ?>
                                    <div class="box">
                                        <h1><?php echo $usuario['followers']; ?></h1>
                                        <p>Followers</p>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($usuario['following'])): ?>
                                    <div class="box">
                                        <h1><?php echo $usuario['following']; ?></h1>
                                        <p>Following</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <a href="buscar_perfil.php?usuario_id=<?php echo $usuario['id']; ?>" class="btn">Ver perfil</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No se encontraron resultados.</p>
        <?php endif; ?>
    </div>
</body>
</html>
