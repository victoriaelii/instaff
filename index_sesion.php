<?php
require_once "config.php";

try {
    $pdo = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener fotos que no han sido eliminadas
$stmt = $pdo->prepare("SELECT * FROM fotos_v WHERE (usuario_subio_id = :user_id OR usuario_subio_id IN (SELECT usuario_siguiendo_id FROM seguidores WHERE usuario_seguidor_id = :user_id)) AND eliminado <> 1 ORDER BY fecha_subido DESC");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COUNT(*) AS total_seguidores FROM seguidores WHERE usuario_siguiendo_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$total_seguidores = $stmt->fetch(PDO::FETCH_ASSOC)['total_seguidores'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS total_seguidos FROM seguidores WHERE usuario_seguidor_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$total_seguidos = $stmt->fetch(PDO::FETCH_ASSOC)['total_seguidos'];

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el ID de la foto y el ID del usuario que dio like
    $foto_id = $_POST['foto_id'];
    $usuario_dio_like_id = $user_id; // Utilizar el ID de usuario de la sesión

    // Insertar el like en la base de datos
    $stmt = $pdo->prepare("INSERT INTO fotos_likes (foto_id, usuario_dio_like_id) VALUES (:foto_id, :usuario_dio_like_id)");
    $stmt->bindParam(':foto_id', $foto_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_dio_like_id', $usuario_dio_like_id, PDO::PARAM_INT);
    $stmt->execute();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index_sesion.css?v=<?php echo time(); ?>">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <title>Inicio</title>
</head>
<style>
       .header {
    grid-area: header;
    background-color:#6818A5 !important;
}
        input[type="text"] {
            width: 300px;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .button {
    display: inline-block;
    padding: 12px 24px; /* Aumentar el padding para hacer el botón más grande */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px; /* Aumentar el tamaño de la fuente */
    text-decoration: none;
    color: white;
    background-color: #AB51E3 !important;
    transition: background-color 0.3s, color 0.3s;
    margin-top: 15px;
    margin-bottom: 10px;
    width: 100px; /* Ancho fijo para hacer el botón más ancho */
}

        .likes {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .likes p {
            margin: 0;
        }

        .contador-likes {
            display: flex;
            align-items: center;
        }

        .contador {
            margin-right: 5px;
        }

        #flor {
    font-size: 30px; /* Tamaño del icono */
    margin-right: 5px; /* Margen a la derecha del icono */
    color:  #AB51E3; /* Color de la flor */
    background-color: transparent; /* Hacer el fondo transparente */
    border: none; /* Eliminar borde */
    padding: 0; /* Eliminar relleno */
}

    .likes button {
        
    border: none;
    background: none;
    padding: 0;
    cursor: pointer;
}

        
</style>
<body>
    <div class="contenedor-principal">
        <div class="header">
            <div class="logo">
                <img src="logo.png">
            </div>

            <div class="centro">
                <a href="#">
                    <span class="material-symbols-outlined">home</span>
                </a>

                <form action="buscar.php" method="GET">
                    <input type="text" name="q" placeholder="Buscar usuarios por nombre o username">
                    <button type="submit" class="button">Buscar</button>
                </form>
            </div>
        </div>

        <div class="barra-lateral">
            <a href="perfil.php">
                <span class="material-symbols-outlined">person</span>
                Mi perfil
            </a>
            <a href="mis_datos.php">
                <span class="material-symbols-outlined">manage_accounts</span>
                Datos Personales
            </a>
            <a href="subir_foto.php">
                <span class="material-symbols-outlined">photo_camera</span>
                Subir Foto
            </a>
            <a href="cambiar_contrasena.php">
                <span class="material-symbols-outlined">lock</span>
                Cambiar Contraseña
            </a>
            <a href="logout.php">
                <span class="material-symbols-outlined">exit_to_app</span>
                Cerrar Sesión
            </a>
        </div>

        <div class="contenido">
        <?php

foreach ($fotos as $foto) {
    echo '<div class="publicaciones">';
    echo '<h1 style="color: #fff;">' . $foto['usuario_subio_username'] . '</h1>';
    echo '<img src="' . APP_ROOT . 'uploads/' . $foto['nombre_archivo'] . '" alt="">';
    echo '<div class="likes">';
    echo '<p class="descripcion">' . $foto['descripcion'] . '</p>';
    echo '<div class="contador-likes">';
    echo '<p class="contador">' . obtenerContadorLikes($foto['id'], $_SESSION['user_id']) . '</p>';
    echo '<form action="like_photo.php" method="POST">';
    echo '<input type="hidden" name="foto_id" value="' . $foto['id'] . '">';
    echo '<input type="hidden" name="usuario_dio_like_id" value="' . $_SESSION['user_id'] . '">';
    if (obtenerContadorLikes($foto['id'], $_SESSION['user_id']) > 0) {
        echo '<button type="submit" name="action" value="unlike"><span id="flor" class="material-symbols-outlined">local_florist</span></button>';
    } else {
        echo '<button type="submit" name="action" value="like"><span id="flor" class="material-symbols-outlined">local_florist</span></button>';
    }
    echo '</form>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}


function obtenerContadorLikes($foto_id, $usuario_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_likes FROM fotos_likes WHERE foto_id = :foto_id AND usuario_dio_like_id = :usuario_id AND eliminado = 0");
    $stmt->bindParam(':foto_id', $foto_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_likes'];
}

?>

        </div>
    </div>

</body>

</html>
