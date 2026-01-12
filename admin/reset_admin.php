<?php
require 'config.php';

// Encriptar la contraseña '123456' correctamente
$password = password_hash('123456', PASSWORD_DEFAULT);

// Insertar o actualizar el admin
$sql = "INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@tienda.com', '$password', 'admin')
        ON DUPLICATE KEY UPDATE password='$password', role='admin'";

$pdo->query($sql);
echo "<h1>Admin reseteado con éxito</h1><p>Usuario: admin@tienda.com<br>Pass: 123456</p><a href='login.php'>Ir al Login</a>";
?>