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

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $foto_id = $_POST['foto_id'];
    $action = $_POST['action'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($action === 'like') {
            $stmt = $pdo->prepare("INSERT INTO fotos_likes (foto_id, usuario_dio_like_id, fecha_hora) VALUES (:foto_id, :user_id, NOW())");
            $stmt->bindParam(':foto_id', $foto_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        } elseif ($action === 'unlike') {
            $stmt = $pdo->prepare("DELETE FROM fotos_likes WHERE foto_id = :foto_id AND usuario_dio_like_id = :user_id");
            $stmt->bindParam(':foto_id', $foto_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        header("Location: index_sesion.php");
        exit();
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}
?>
