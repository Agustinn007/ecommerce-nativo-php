<?php
require '../includes/config.php';

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
<?php if(empty($cartItems)): ?>
    <div class="text-center py-5">
        <i class="bi bi-cart-x fs-1 text-muted"></i>
        <p class="mt-3 text-muted">Tu carrito está vacío</p>
        <button type="button" class="btn btn-outline-primary rounded-pill" data-bs-dismiss="offcanvas">Seguir mirando</button>
    </div>
<?php else: ?>
    <ul class="list-group list-group-flush mb-3">
        <?php foreach ($cartItems as $item): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <div class="d-flex align-items-center">
                <img src="<?= BASE_URL ?>uploads/<?= $item['image'] ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" class="me-3">
                <div>
                    <h6 class="mb-0 text-truncate" style="max-width: 150px;"><?= htmlspecialchars($item['name']) ?></h6>
                    <small class="text-muted"><?= $item['qty'] ?> x $<?= number_format($item['price'], 2) ?></small>
                </div>
            </div>
            <span class="fw-bold">$<?= number_format($item['qty'] * $item['price'], 2) ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0">Total</h5>
        <h4 class="fw-bold text-primary m-0">$<?= number_format($total, 2) ?></h4>
    </div>
    <div class="d-grid gap-2">
        <a href="checkout.php" class="btn btn-primary rounded-pill py-2 fw-bold">Iniciar Compra</a>
        <a href="carrito.php" class="btn btn-outline-secondary rounded-pill py-2">Ver Carrito Completo</a>
    </div>
<?php endif; ?>