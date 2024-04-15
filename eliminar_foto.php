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
