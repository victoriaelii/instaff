<?php
session_start();

require_once "config.php";
require_once "data_access/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $conn = getDbConnection(); 

    $query = "SELECT id, username, password, password_salt FROM usuarios WHERE username = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Concatenar la contraseña con el salt
        $password_with_salt = $password . $user['password_salt'];
        // Encriptar la contraseña con salt
        $password_encrypted = strtoupper(hash('sha512', $password_with_salt));

        // Comparar contraseñas
        if ($password_encrypted === $user['password']) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            // Redirigir al usuario a la página de bienvenida después de iniciar sesión correctamente
            header("Location: index_sesion.php");
            exit();
        } else {
            // Usuario o contraseña incorrectos, mostrar ventana emergente
            echo '<script>alert("Usuario o contraseña incorrectos. Intenta nuevamente.");</script>';
        }
    } else {
        // Usuario no encontrado, mostrar ventana emergente y redirigir a login.html
        echo '<script>alert("Usuario no encontrado. Intenta nuevamente.");';
        echo 'window.location.href = "login.html";</script>';
    }
}
?>
