<?php
require 'config/databaseconnect.php';

// Encriptar la contraseña
$hashed_password = password_hash('DARyenis1!', PASSWORD_DEFAULT);

// Insertar un usuario administrador
$stmt = $mysqli->prepare("INSERT INTO users (username, contrasena, rol) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashed_password, $rol);

$username = 'maic'; // Nombre de usuario del administrador
$rol = 'admin'; // Rol del usuario (admin en este caso)
$stmt->execute();
$stmt->close();

$mysqli->close();

echo "Usuario administrador creado exitosamente.";
?>
