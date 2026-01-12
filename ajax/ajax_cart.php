<?php
require '../includes/config.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Error en la solicitud']);
    exit;
}

// Verificar stock en base de datos
$stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product || ($product['stock'] ?? 0) <= 0) {
    echo json_encode(['success' => false, 'message' => 'Este producto no tiene stock disponible.']);
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]++;
} else {
    $_SESSION['cart'][$id] = 1;
}

echo json_encode(['success' => true, 'count' => array_sum($_SESSION['cart'])]);
?>