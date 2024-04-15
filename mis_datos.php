<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    // El usuario no ha iniciado sesión, redirigir a index.html
    header("Location: index.html");
    exit();
}
?><?php
session_start();

require_once "config.php";
require_once "data_access/db.php";

$error_msg = '';
$conn = getDbConnection(); 

$query = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(":id", $_SESSION['user_id']);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $apellidos = trim($_POST["apellidos"]);
    $genero = $_POST["genero"];
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $email = trim($_POST["email"]);

    if (empty($nombre) || empty($email)) {
        $error_msg = "El nombre y el email son campos obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "El formato del email no es válido.";
    } else {
        $query = "UPDATE usuarios SET nombre = :nombre, apellidos = :apellidos, genero = :genero, fecha_nacimiento = :fecha_nacimiento WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellidos", $apellidos);
        $stmt->bindParam(":genero", $genero);
        $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
        $stmt->bindParam(":id", $_SESSION['user_id']);

        if ($stmt->execute()) {
            header("location: index_sesion.php");
            exit();
        } else {
            $error_msg = "Error al actualizar los datos. Por favor, inténtelo de nuevo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Datos</title>
    <link href="<?=APP_ROOT?>css/mis_datos.css" rel="stylesheet" type="text/css" /> 
</head>
<style>
    .button-container {
        text-align: center;
        margin-top: 5px;
    }

    .button {
        display: inline-block;
        width: 120px;
        padding: 8px 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 11px;
        text-decoration: none;
        color: white;
        background-color: #6818A5 !important;
        transition: background-color 0.3s, color 0.3s;
        margin-right: 10px;
    }

    .button:hover {
        background-color: #AB51E3 !important;
    }

    .button-subir {
        background-color: #6818A5 !important;
        color: white !important;
        display: inline-block;
        width: 140px;
        height: 30px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 11px;
        text-decoration: none;
        color: white;
        background-color: #6818A5 !important;
        transition: background-color 0.3s, color 0.3s;
        margin-right: 10px;
        margin-top: -5px;
        margin-bottom: 10px;
    }

    .button-subir:hover {
        background-color: #AB51E3 !important;
    }
</style>
<body>
<div class="container">
    <div class="header">
    <h2>Modificar datos personales</h2>
    </div>
    <div class="row">
        <div class="leftcolumn">
            <div class="card">
                <h5>Actualice su información personal:</h5>
                <?php if ($error_msg != ''): ?>
                    <div class="error"><?= $error_msg ?></div>
                <?php endif; ?>
                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
                    <label for="txt-nombre">Nombre:</label><br>
                    <input type="text" name="nombre" id="txt-nombre" value="<?= isset($usuario['nombre']) ? $usuario['nombre'] : '' ?>" required><br>
                    <label for="txt-apellidos">Apellidos:</label><br>
                    <input type="text" name="apellidos" id="txt-apellidos" value="<?= isset($usuario['apellidos']) ? $usuario['apellidos'] : '' ?>"><br>
                    <label for="sel-genero">Género:</label><br>
                    <select name="genero" id="sel-genero">
                        <option value="M" <?= isset($usuario['genero']) && $usuario['genero'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= isset($usuario['genero']) && $usuario['genero'] === 'F' ? 'selected' : '' ?>>Femenino</option>
                        <option value="X" <?= isset($usuario['genero']) && $usuario['genero'] === 'X' ? 'selected' : '' ?>>Prefiero no especificar</option>
                    </select><br>
                    <label for="txt-fecha-nacimiento">Fecha de Nacimiento:</label><br>
                    <input type="date" name="fecha_nacimiento" id="txt-fecha-nacimiento" value="<?= isset($usuario['fecha_nacimiento']) ? $usuario['fecha_nacimiento'] : '' ?>"><br>
                    <label for="txt-email">Email:</label><br>
                    <input type="email" name="email" id="txt-email" value="<?= isset($usuario['email']) ? $usuario['email'] : '' ?>" required><br><br>
                    <div class="button-container">
                    <input type="submit" value="Guardar Cambios" class="button-subir">
                     </div>
                </form>
                <div class="button-container">
                <a href="cambiar_contrasena.php" class="button" style="width: 150px;">Cambiar Contraseña</a>
                    <a href="index_sesion.php" class="button">Regresar</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

