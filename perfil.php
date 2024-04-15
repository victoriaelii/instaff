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

// Obtener la cantidad de seguidores y seguidos
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_seguidores FROM seguidores WHERE usuario_siguiendo_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$total_seguidores = $stmt->fetch(PDO::FETCH_ASSOC)['total_seguidores'];
$stmt = $pdo->prepare("SELECT COUNT(*) AS total_seguidos FROM seguidores WHERE usuario_seguidor_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$total_seguidos = $stmt->fetch(PDO::FETCH_ASSOC)['total_seguidos'];

// Verificar si se recibió un ID de foto para eliminar
if(isset($_POST['photo_id'])){
    // Obtener el ID de la foto a eliminar
    $photo_id = $_POST['photo_id'];

    // Conectar a la base de datos
    try {
        $pdo = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Actualizar el registro de la foto para marcarlo como eliminado
        $stmt = $pdo->prepare("UPDATE fotos SET eliminado = 1 WHERE id = :id");
        $stmt->bindParam(':id', $photo_id, PDO::PARAM_INT);
        $stmt->execute();

        // Devolver una respuesta de éxito
        echo json_encode(array('success' => true));
    } catch (PDOException $e) {
        // Devolver una respuesta de error
        echo json_encode(array('success' => false, 'message' => 'Error al eliminar la foto: ' . $e->getMessage()));
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Perfil</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
    <link rel="stylesheet" href="css/perfil.css" />
</head>
<style>

    .profile-stats{
    margin-top: 10px;
    margin-bottom: 10px;
    padding: 2rem 0;
    text-align: center; /* Alinea el texto a la derecha */
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
                        <h1 class="profile-user-name profile-real-name"><?php echo $user['username']; ?></h1>

                        </div>
                    </div>
                    <div class="profile-stats">
                        <?php
                            echo '<div class="profile-stats">';
                            echo '<ul>';
                            echo '<li><span class="profile-stat-count">' . $user['nombre'] . ' ' . $user['apellidos'] . '</span> Nombre</li>';
                            echo '<li><span class="profile-stat-count">' . ($user['genero'] == 'M' ? 'Hombre' : 'Mujer') . '</span> Género</li>';
                            echo '<li><span class="profile-stat-count">' . date('d/m/Y', strtotime($user['fecha_nacimiento'])) . '</span> Nacimiento</li>';
                            echo '</ul>';
                            echo '<ul>';
                            echo '<li class="text-center"><span class="profile-stat-count">' . $total_seguidores . '</span> followers</li>';
                            echo '<li class="text-center"><span class="profile-stat-count">' . $total_seguidos . '</span> following</li>';
                            echo '</ul>';
                            echo '</div>';

                        ?>
                    </div>
                </div>
            </div>
    </header>
    <main>

        <div class="container">
            <div class="gallery">
            <?php

$user_id = $_SESSION['user_id'];

try {
    $pdo = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM fotos WHERE usuario_subio_id = :user_id AND eliminado = 0 ORDER BY fecha_subido DESC");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    while ($foto = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $foto_id = $foto['id'];
        // Consultar la cantidad de likes para esta foto
        $stmt_likes = $pdo->prepare("SELECT COUNT(*) AS total_likes FROM fotos_likes WHERE foto_id = :foto_id");
        $stmt_likes->bindParam(':foto_id', $foto_id, PDO::PARAM_INT);
        $stmt_likes->execute();
        $total_likes = $stmt_likes->fetch(PDO::FETCH_ASSOC)['total_likes'];
        echo '<div class="gallery-item" tabindex="0" data-photo-id="' . $foto['id'] . '">';
        echo '<div class="gallery-item-info">';
        echo '<ul>';   
        echo '<li class="gallery-item-likes"><span class="visually-hidden">Likes:</span><span id="flor" class="material-symbols-outlined">local_florist</span> ' . $total_likes . '</li>';
        echo '</ul>';
        echo '</div>';
        echo '<img src="' . APP_ROOT . 'uploads/' . $foto['nombre_archivo'] . '" class="gallery-image" alt="">';
        echo '</div>';
        
    }
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
            </div>
        </div>

<!-- Modal-->
<div class="modal-container" id="myModal">
    <div class="modal">
        <span class="close">&times;</span>
        <div style="text-align: center;">
            <img class="modal-content" id="img01">
        </div>
        <div class="eliminarImagen">
            <button class="eliminarBtn">Eliminar</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("img01");
    var items = document.querySelectorAll(".gallery-item");

    items.forEach(function(item) {
        item.addEventListener("click", function() {
            modal.style.display = "block";
            modalImg.src = this.getElementsByTagName("img")[0].src;
            document.body.classList.add("modal-open");

            // Obtener el ID de la foto seleccionada y almacenarlo en el botón de eliminar
            var photoId = this.getAttribute('data-photo-id');
            $('.eliminarBtn').attr('data-photo-id', photoId);
        });
    });

    var closeBtn = document.getElementsByClassName("close")[0];
    closeBtn.addEventListener("click", function() {
        modal.style.display = "none";
        document.body.classList.remove("modal-open");
    });

    $(document).ready(function(){
        $('.eliminarBtn').click(function(){
            // Obtener el ID de la foto a eliminar desde el atributo data-photo-id del botón
            var photo_id = $(this).attr('data-photo-id');

            // Enviar solicitud AJAX al controlador PHP para eliminar la foto
            $.ajax({
                url: 'eliminar_foto.php',
                type: 'POST',
                data: {photo_id: photo_id},
                dataType: 'json',
                success: function(response){
                    if(response.success){
                        // Foto eliminada correctamente, cerrar el modal y recargar la página
                        modal.style.display = "none";
                        document.body.classList.remove("modal-open");
                        location.reload();
                    } else {
                        // Error al eliminar la foto, mostrar un mensaje de error
                        alert('Error al eliminar la foto: ' + response.message);
                    }
                },
                error: function(){
                    // Error al hacer la solicitud AJAX
                    alert('Error al eliminar la foto. Por favor, intenta de nuevo más tarde.');
                }
            });
        });
    });
</script>



    </main>
</body>
</html>
