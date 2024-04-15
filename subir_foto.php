<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Verifica si se ha enviado un formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["foto"]) && isset($_POST["descripcion"])) {
    require_once "config.php";
    require_once "data_access/db.php";

    try {
        $conn = getDbConnection();

        $usuario_subio_id = $_SESSION['user_id'];
        $nombre_archivo = $_FILES["foto"]["name"];
        $tamaño = $_FILES["foto"]["size"];
        $descripcion = $_POST["descripcion"];

        // Ruta de destino para guardar las fotos
        $ruta_destino = "uploads/" . $nombre_archivo;

        // Verifica si el archivo es una imagen
        $tipo_archivo = strtolower(pathinfo($ruta_destino, PATHINFO_EXTENSION));
        if ($tipo_archivo != "jpg" && $tipo_archivo != "jpeg" && $tipo_archivo != "png" && $tipo_archivo != "gif") {
            $error_msg = "Solo se permiten archivos de imagen JPG, JPEG, PNG y GIF.";
        } else {
            // Verifica si el archivo se ha subido correctamente
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $ruta_destino)) {
                // Genera un ID único para la foto
                $secure_id = uniqid();

                // Prepara la consulta SQL para insertar la foto en la base de datos
                $query = "INSERT INTO fotos (secure_id, usuario_subio_id, nombre_archivo, tamaño, descripcion, fecha_subido) VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($query);

                // Ejecuta la consulta con los datos proporcionados
                if ($stmt->execute([$secure_id, $usuario_subio_id, $nombre_archivo, $tamaño, $descripcion])) {
                    $success_msg = "La foto se ha subido correctamente.";
                } else {
                    $error_msg = "Error al subir la foto. Por favor, inténtelo de nuevo.";
                }
            } else {
                $error_msg = "Error al subir la foto. Por favor, inténtelo de nuevo.";
            }
        }
    } catch (PDOException $e) {
        $error_msg = "Error al conectar con la base de datos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/subir_foto.css" rel="stylesheet" type="text/css" />
    <title>Subir Foto</title>
    <style>
.uploaded-image {
    max-width: 100px; /* Establece el ancho máximo deseado */
    height: auto; /* Mantén la proporción de aspecto */
    display: block; /* Para centrar la imagen */
    margin: 0 auto; /* Para centrar la imagen */
}
.photo-container {
    text-align: center;
}

.photo-description {
    text-align: center;
}
.photo-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    grid-gap: 20px;
    justify-content: center;
}

.photo-container div {
    text-align: center;
}


    </style>
</head>
<body>
    <div class="container">
        <h2 class="header">Subir Foto</h2>
        <?php if (isset($error_msg)): ?>
            <div style="color: red;"><?php echo $error_msg; ?></div>
        <?php elseif (isset($success_msg)): ?>
            <div style="color: green;"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <!-- Formulario de carga de fotos -->
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" class="form">
    <label for="foto">Seleccione una foto:</label><br>
    <input type="file" id="foto" name="foto" accept="image/*" required><br><br>
    <label for="descripcion">Descripción (opcional):</label><br>
    <textarea id="descripcion" name="descripcion" rows="4" cols="50" maxlength="1024"></textarea><br><br>
    <input type="submit" value="Subir Foto" class="button-subir">
</form>

        <hr>
        <!-- Mostrar las fotos subidas por el usuario -->
        <?php
        require_once "config.php";
        require_once "data_access/db.php";

        $conn = getDbConnection();
        $usuario_subio_id = $_SESSION['user_id'];

        $query = "SELECT nombre_archivo, descripcion FROM fotos WHERE usuario_subio_id = ? AND eliminado = 0";
$stmt = $conn->prepare($query);
$stmt->execute([$usuario_subio_id]);
$fotos_usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if (!empty($fotos_usuario)) {
            echo "<h3>Fotos subidas por ti:</h3>";
            echo "<div class='photo-container'>";
            foreach ($fotos_usuario as $foto) {
                echo "<div>";
                echo "<img src='uploads/{$foto['nombre_archivo']}' alt='Foto' class='uploaded-image'>";
                echo "<p class='photo-description'>{$foto['descripcion']}</p>";
                echo "</div>";
            }
            echo "</div>";
        }
        

        ?>
        <p><a href="index_sesion.php" class="button-volver" style="width: 170px; text-align: center;">Volver a la página principal</a></p>
    </div>
</body>
</html>
