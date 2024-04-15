<?php
// Incluir archivo de configuración y conexión a la base de datos
require_once "config.php";

// Conectar a la base de datos
try {
    $pdo = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Verificar si el usuario está autenticado
session_start();
if (!isset($_SESSION['user_id'])) {
    // Si el usuario no está autenticado, redirigir a la página de inicio de sesión
    header("Location: index.html");
    exit();
}

// Obtener el ID del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Query para obtener los datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Query para obtener las fotos del usuario
$stmt = $pdo->prepare("SELECT * FROM fotos WHERE usuario_subio_id = :user_id AND eliminado = 0 ORDER BY fecha_subido DESC");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$fotos_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener el ID del usuario buscado de la URL
if (isset($_GET['usuario_id'])) {
    $usuario_id = $_GET['usuario_id'];

    // Query para obtener los datos del usuario buscado
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Query para obtener las fotos del usuario buscado
    $stmt = $pdo->prepare("SELECT * FROM fotos WHERE usuario_subio_id = :usuario_id AND eliminado = 0 ORDER BY fecha_subido DESC");
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $fotos_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function isFollowing($pdo, $user_id, $usuario_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE usuario_seguidor_id = :user_id AND usuario_siguiendo_id = :usuario_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    return $count > 0;
}
// Obtener la cantidad de seguidores y seguidos
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_seguidores FROM seguidores WHERE usuario_siguiendo_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$total_seguidores = $stmt->fetch(PDO::FETCH_ASSOC)['total_seguidores'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS total_seguidos FROM seguidores WHERE usuario_seguidor_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$total_seguidos = $stmt->fetch(PDO::FETCH_ASSOC)['total_seguidos'];

// Obtener la cantidad de likes de cada foto
$stmt = $pdo->prepare("SELECT foto_id, COUNT(*) AS total_likes FROM fotos_likes GROUP BY foto_id");
$stmt->execute();
$likes_por_foto = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $likes_por_foto[$row['foto_id']] = $row['total_likes'];
}

// Agregar la cantidad de likes a cada foto en el array $fotos_usuario
foreach ($fotos_usuario as &$foto) {
    $foto['total_likes'] = isset($likes_por_foto[$foto['id']]) ? $likes_por_foto[$foto['id']] : 0;
}
unset($foto); // Liberar la referencia de la última iteración

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Perfil</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <link rel="stylesheet" href="css/buscar_perfil.css" />
</head>
<style>
    .profile-image img {
    width: 200px; /* Cambia el ancho de la imagen */
    height: 200px; /* Cambia la altura de la imagen */
    border-radius: 50%; /* Hace que la imagen tenga bordes redondeados */
    object-fit: cover; /* Ajusta la imagen al contenedor sin distorsionarse */
    background-color: #AB51E3; /* Color de fondo */
    object-fit: contain;
}

</style>
<body>
    <header>
        <div class="container-todo">
            <div class="container">
                <div class="profile">
                    <a href="index_sesion.php" class="button">Regresar</a>
                    
                    <div class="profile-image">
                    <img src="uploads/perfil.png" alt="Descripción de la imagen">
                    </div>
                    
                    <div class="profile-user-settings">
                        <div class="profile-bio">
                            <h1 class="profile-user-name profile-real-name"><?php echo $usuario['username']; ?></h1>
                            
                            <?php if ($usuario_id != $user_id): // Mostrar botones solo si no es el perfil del usuario actual ?>
    <form action="seguir.php" method="POST">
        <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">
        <?php if (isFollowing($pdo, $user_id, $usuario_id)): ?>
            <button type="submit" name="dejar_seguir" class="button">Dejar de seguir</button>
        <?php else: ?>
            <button type="submit" name="seguir" class="button">Seguir</button>
        <?php endif; ?>
    </form>
<?php endif; ?>
                        </div>
                    </div>
                    <div class="profile-stats">
                        <ul>
                            <li><span class="profile-stat-count"><?php echo $usuario['nombre'] . ' ' . $usuario['apellidos']; ?></span> Nombre</li>
                            <li><span class="profile-stat-count"><?php echo ($usuario['genero'] == 'M' ? 'Hombre' : 'Mujer'); ?></span> Género</li>
                            <li><span class="profile-stat-count"><?php echo date('d/m/Y', strtotime($usuario['fecha_nacimiento'])); ?></span> Nacimiento</li>
                        </ul>
                        <ul>
                        <li class="text-center"><span class="profile-stat-count"><?php echo $total_seguidores; ?></span> followers</li>
            <li class="text-center"><span class="profile-stat-count"><?php echo $total_seguidos; ?></span> following</li>
                        </ul>
                    </div>
                </div>
            </div>
    </header>
    <main>
    <?php if (!empty($fotos_usuario)): ?>
    <div class="container">
        <div class="gallery">
            <?php foreach ($fotos_usuario as $foto): ?>
                <div class="gallery-item" tabindex="0">
                    <img src="<?php echo APP_ROOT . 'uploads/' . $foto['nombre_archivo']; ?>" class="gallery-image" alt="">
                    <div class="gallery-item-info">
                        <ul>
                            <li class="gallery-item-likes"><span class="visually-hidden">Likes:</span><i class="fas fa-heart" aria-hidden="true"></i> <?php echo $foto['total_likes']; ?></li>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

    </main>
</body>
</html>
