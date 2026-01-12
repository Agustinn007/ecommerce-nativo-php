<?php
require '../config.php';

try {
    // Intentar agregar la columna 'stock'
    $sql = "ALTER TABLE products ADD COLUMN stock INT DEFAULT 0";
    $pdo->exec($sql);
    echo "<h1>¡Éxito!</h1><p>La columna 'stock' ha sido agregada correctamente.</p>";
    echo "<a href='index.php'>Volver al Panel</a>";
} catch (PDOException $e) {
    // Si ya existe o hay otro error
    echo "<h1>Aviso</h1><p>Probablemente la columna ya existía o hubo un error:</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<a href='index.php'>Volver al Panel</a>";
}
?>