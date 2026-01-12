<?php
// Definir rutas base del sistema
define('ROOT_PATH', dirname(__DIR__) . '/'); // Ruta física (ej: C:\xampp\htdocs\e-commerce-basic\)
define('BASE_URL', 'http://localhost/e-commerce-basic/'); // URL web (ajusta si cambias el nombre de la carpeta)

$host = 'localhost';
$dbname = 'tienda_php';
$username = 'root'; // Cambia esto si tienes otro usuario
$password = '';     // Cambia esto si tienes contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    session_start(); // Iniciar sesión en todas las páginas
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
