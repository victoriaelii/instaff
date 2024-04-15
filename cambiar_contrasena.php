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
require_once "config.php";
require_once "data_access/db.php";

$error_msg = '';
$conn = getDbConnection(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST["old_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Verificar que las contraseñas coincidan
    if ($new_password !== $confirm_password) {
        $error_msg = "Las contraseñas nuevas no coinciden.";
    } else {
        // Obtener la contraseña actual del usuario
        $query = "SELECT password, password_salt FROM usuarios WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id", $_SESSION['user_id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar que la contraseña actual ingresada sea correcta
        $old_password_with_salt = $old_password . $row['password_salt'];
        $old_password_encrypted = strtoupper(hash('sha512', $old_password_with_salt));
        
        if ($old_password_encrypted !== $row['password']) {
            $error_msg = "La contraseña actual es incorrecta.";
        } else {
            // Generar un nuevo salt y encriptar la nueva contraseña
            $new_salt = bin2hex(random_bytes(16));
            $new_password_with_salt = $new_password . $new_salt;
            $new_password_encrypted = strtoupper(hash('sha512', $new_password_with_salt));

            // Actualizar la contraseña en la base de datos
            $query = "UPDATE usuarios SET password = :new_password, password_salt = :new_salt WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":new_password", $new_password_encrypted);
            $stmt->bindParam(":new_salt", $new_salt);
            $stmt->bindParam(":id", $_SESSION['user_id']);

            if ($stmt->execute()) {
                // Redirigir a la página de bienvenida después de cambiar la contraseña
                header("location: login.html");
                exit();
            } else {
                $error_msg = "Error al cambiar la contraseña. Por favor, inténtelo de nuevo.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link href="css/cambiar_contrasena.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Cambiar Contraseña</h2>
    </div>
    <div class="row">
        <div class="leftcolumn">
            <div class="card">
                <?php if ($error_msg != ''): ?>
                    <div class="error"><?= $error_msg ?></div>
                <?php endif; ?>
                <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
                    <label for="old_password">Contraseña Actual:</label><br>
                    <input type="password" name="old_password" id="old_password" required><br><br>
                    <label for="new_password">Nueva Contraseña:</label><br>
                    <input type="password" name="new_password" id="new_password" minlength="6" required><br><br>
                    <label for="confirm_password">Confirmar Nueva Contraseña:</label><br>
                    <input type="password" name="confirm_password" id="confirm_password" minlength="6" required><br><br>
                    <div class="button-container">
    <input type="submit" value="Cambiar Contraseña" class="button-subir">
</div>

                </form>
                <a href="mis_datos.php" class="button">Regresar</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
