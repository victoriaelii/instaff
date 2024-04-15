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
// Incluir archivo de configuración y conexión a la base de datos
require_once "config.php";
$pdo = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);

// Verificar si el usuario está autenticado
session_start();
if (!isset($_SESSION['user_id'])) {
    // Si el usuario no está autenticado, redirigir a la página de inicio de sesión
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Obtener el ID del usuario a seguir desde el formulario
if (isset($_POST['usuario_id'])) {
    $usuario_id_a_seguir = $_POST['usuario_id'];

    // Query para seguir o dejar de seguir al usuario
    $stmt = $pdo->prepare("SELECT * FROM seguidores WHERE usuario_seguidor_id = :usuario_seguidor_id AND usuario_siguiendo_id = :usuario_siguiendo_id");
    $stmt->bindParam(':usuario_seguidor_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_siguiendo_id', $usuario_id_a_seguir, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Si ya está siguiendo al usuario, dejar de seguirlo
        $stmt = $pdo->prepare("DELETE FROM seguidores WHERE usuario_seguidor_id = :usuario_seguidor_id AND usuario_siguiendo_id = :usuario_siguiendo_id");
        $stmt->bindParam(':usuario_seguidor_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_siguiendo_id', $usuario_id_a_seguir, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Si no está siguiendo al usuario, seguirlo
        $stmt = $pdo->prepare("INSERT INTO seguidores (usuario_seguidor_id, usuario_siguiendo_id, fecha_hora) VALUES (:usuario_seguidor_id, :usuario_siguiendo_id, NOW())");
        $stmt->bindParam(':usuario_seguidor_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_siguiendo_id', $usuario_id_a_seguir, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Redirigir a la página de perfil del usuario seguido
    header("Location: perfil.php?usuario_id=" . $usuario_id_a_seguir);
    exit();
} else {
    // Si no se proporcionó un ID de usuario a seguir, redirigir a la página de inicio
    header("Location: index.php");
    exit();
}
