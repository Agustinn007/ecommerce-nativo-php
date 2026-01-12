<?php
require 'config.php';

$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $subtotal = $p['price'] * $qty;
        $total += $subtotal;
        $p['qty'] = $qty;
        $cartItems[] = $p;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Carrito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }
        .btn-primary { background: linear-gradient(45deg, #667eea, #764ba2); border: none; box-shadow: 0 4px 15px rgba(118, 75, 162, 0.4); }
        .btn-primary:hover { filter: brightness(1.1); }
    </style>
</head>
<body class="container mt-5">
    <h2 class="mb-4 fw-bold">Tu Carrito de Compras</h2>
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="uploads/<?= $item['image'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" class="me-3">
                                    <span class="fw-semibold"><?= htmlspecialchars($item['name']) ?></span>
                                </div>
                            </td>
                            <td><?= $item['qty'] ?></td>
                            <td class="text-end fw-bold">$<?= number_format($item['qty'] * $item['price'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($cartItems)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">Tu carrito está vacío</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <h4 class="fw-bold m-0">Total: $<?= number_format($total, 2) ?></h4>
                <div>
                    <a href="index.php" class="btn btn-outline-secondary me-2 rounded-pill">Seguir comprando</a>
                    <?php if ($total > 0): ?>
                        <a href="checkout.php" class="btn btn-primary rounded-pill px-4 fw-bold">Proceder al Pago</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
