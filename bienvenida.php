<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    // El usuario no ha iniciado sesión, redirigir a index.html
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida</title>
</head>
<body>
    <h2>Bienvenido</h2>
    <?php if(isset($_SESSION['username'])): ?>
        <p>¡Hola, <?php echo $_SESSION['username']; ?>!</p>
    <?php else: ?>
        <p>Bienvenido</p>
    <?php endif; ?>
    <p>Has iniciado sesión correctamente.</p>
    <!-- Aquí puedes colocar cualquier contenido adicional que desees mostrar -->
    <p><a href="mis_datos.php">Modificar Mis Datos</a></p>
</body>
</html>
