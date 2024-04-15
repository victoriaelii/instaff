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
session_start();

require_once "data_access/db.php"; // Include the file containing getDbConnection() function

// Eliminar la relación de seguimiento de la base de datos
$stmt = $pdo->prepare("DELETE FROM seguidores WHERE usuario_seguidor_id = :user_id AND usuario_siguiendo_id = :usuario_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
$stmt->execute();
header("Location: perfil.php?usuario_id=$usuario_id");
exit();

?>