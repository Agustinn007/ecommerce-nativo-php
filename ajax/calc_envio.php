<?php
// Simulación de respuesta de API (Andreani / Correo Argentino)
header('Content-Type: application/json');
$cp = $_GET['cp'] ?? '';
$costo = 0;

if($cp) {
    // Lógica simulada: CP que empieza con 1 (CABA) es más barato
    // En producción aquí conectarías con la API real usando cURL
    if(substr($cp, 0, 1) == '1') {
        $costo = 5500; // Precio CABA
    } else {
        $costo = 9800; // Precio Interior
    }
}

echo json_encode(['costo' => $costo]);
?>
