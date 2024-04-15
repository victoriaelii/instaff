<?php
session_start();

require_once "config.php";
require_once "data_access/db.php";

$error_msg = '';
$conn = getDbConnection(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $nombre = trim($_POST["nombre"]);
    $apellidos = trim($_POST["apellidos"]);
    $genero = $_POST["genero"];
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $email = trim($_POST["email"]);

    if (empty($username) || empty($password) || empty($confirm_password) || empty($nombre) || empty($apellidos) || empty($genero) || empty($fecha_nacimiento) || empty($email)) {
        $error_msg = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "El email no es válido.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Las contraseñas no coinciden.";
    } else {
        $query = "SELECT id FROM usuarios WHERE username = :username";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Muestra la alerta de que el usuario ya está en uso
            echo '<script>alert("Este usuario ya está en uso. Por favor, ingresa otro.");';
            // Redirige nuevamente a register.html después de hacer clic en "Aceptar"
            echo 'window.addEventListener("load", function() {';
            echo '    window.location.href = "register.html";';
            echo '});</script>';
            // Sal del script
            exit();
        }
        else {
            // Generar salt aleatorio
            $salt = bin2hex(random_bytes(16)); // Reducir la longitud del salt a 16 bytes
            // Concatenar la contraseña con el salt
            $password_with_salt = $password . $salt;
            // Encriptar la contraseña con salt
            $password_encrypted = strtoupper(hash('sha512', $password_with_salt));

            $query = "INSERT INTO usuarios (username, password, password_salt, nombre, apellidos, genero, fecha_nacimiento, email, fecha_hora_registro, activo) VALUES (:username, :password_encrypted, :salt, :nombre, :apellidos, :genero, :fecha_nacimiento, :email, NOW(), 1)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password_encrypted", $password_encrypted);
            $stmt->bindParam(":salt", $salt);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":apellidos", $apellidos);
            $stmt->bindParam(":genero", $genero);
            $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
            $stmt->bindParam(":email", $email);

            if ($stmt->execute()) {
                // Si la ejecución es exitosa, redirige al usuario a la página de inicio de sesión
                header("location: login.html");
                exit();
            } else {
                $error_msg = "Error al registrar el usuario. Por favor, inténtelo de nuevo.";
            }
        }
    }
}

// Si llegamos aquí, significa que hay un error. Redirigir de vuelta a register.html con el mensaje de error
$_SESSION['error_msg'] = $error_msg;
?>
