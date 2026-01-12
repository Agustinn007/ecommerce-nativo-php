<?php
require 'includes/config.php';

// Calcular total (reutilizamos lógica simple)
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = $pdo->query("SELECT id, price FROM products WHERE id IN ($ids)");
    while ($row = $stmt->fetch()) {
        $total += $row['price'] * $_SESSION['cart'][$row['id']];
    }
}

// Obtener datos del usuario si está logueado
$userData = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $guestName = $_POST['name'] ?? null;
    $guestEmail = $_POST['email'] ?? null;
    $province = $_POST['province'] ?? '';
    $city = $_POST['city'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] . ", " . $city . ", " . $province . " - CP: " . $_POST['postal_code'] . " - DNI: " . $_POST['dni'] . " - Tel: " . $phone;
    
    // Sumar costo de envío al total (recibido del hidden o recalculado)
    $shippingCost = floatval($_POST['shipping_cost'] ?? 0);

    // Aplicar envío gratis si supera 100000
    if ($total > 100000) {
        $shippingCost = 0;
    }

    $finalTotal = $total + $shippingCost;

    // 1. Insertar Orden
    $sql = "INSERT INTO orders (user_id, guest_name, guest_email, guest_address, total) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $guestName, $guestEmail, $address, $finalTotal]);
    $orderId = $pdo->lastInsertId();

    // 2. Insertar Items
    foreach ($_SESSION['cart'] as $productId => $qty) {
        // Obtener precio actual del producto para congelarlo en la orden
        $pStmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $pStmt->execute([$productId]);
        $price = $pStmt->fetchColumn();

        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmtItem->execute([$orderId, $productId, $qty, $price]);
    }

    // 3. Limpiar carrito y redirigir
    unset($_SESSION['cart']);
    header("Location: gracias.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Finalizar Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }
        .form-control, .input-group-text { border-radius: 10px; padding: 12px; border: 1px solid #eee; }
        .input-group-text { background: white; border-right: none; color: #764ba2; }
        .form-control { border-left: none; }
        .form-control:focus { border-color: #eee; box-shadow: none; background-color: #fdfdfd; }
        .input-group:focus-within { box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.15); border-radius: 10px; }
        .btn-success { background: linear-gradient(45deg, #11998e, #38ef7d); border: none; box-shadow: 0 4px 15px rgba(56, 239, 125, 0.4); transition: transform 0.2s; }
        .btn-success:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="container mt-5">
    <div class="row g-5">
        <!-- Columna Izquierda: Formulario -->
        <div class="col-md-7">
            <h2 class="mb-4 fw-bold"><i class="bi bi-truck text-primary"></i> Detalles de Envío</h2>
            <form method="POST" class="card p-4 shadow-sm border-0 rounded-4">
                <input type="hidden" name="shipping_cost" id="shipping_cost_input" value="0">
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="alert alert-info rounded-3"><i class="bi bi-person-check"></i> Comprando como usuario registrado.</div>
                    <!-- Campos ocultos para mantener compatibilidad o visibles para editar -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted fw-bold">NOMBRE</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($userData['name'] . ' ' . ($userData['lastname'] ?? '')) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted fw-bold">EMAIL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($userData['email']) ?>" required>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-4">
                        ¿Ya tienes cuenta? <a href="login.php" class="fw-bold text-decoration-none">Iniciar sesión</a>
                    </div>
                    <h5 class="mb-3">Datos de Contacto</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted fw-bold">NOMBRE COMPLETO</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted fw-bold">EMAIL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label small text-muted fw-bold">PROVINCIA</label>
                        <select name="province" id="province" class="form-select" data-city-target="city" data-city-value="<?= htmlspecialchars($userData['city'] ?? '') ?>" required></select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label small text-muted fw-bold">CIUDAD</label>
                        <select name="city" id="city" class="form-select" required><option value="">Selecciona provincia...</option></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label small text-muted fw-bold">DNI</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                            <input type="text" name="dni" class="form-control" value="<?= htmlspecialchars($userData['dni'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small text-muted fw-bold">TELÉFONO</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>" required placeholder="Ej: 11 1234 5678">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label small text-muted fw-bold">CÓDIGO POSTAL</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" name="postal_code" id="postal_code" class="form-control" value="<?= htmlspecialchars($userData['postal_code'] ?? '') ?>" required placeholder="Ej: 1414">
                        </div>
                        <div id="shipping_feedback" class="form-text text-primary fw-bold"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small text-muted fw-bold">DIRECCIÓN DE ENTREGA</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-house-door"></i></span>
                        <textarea name="address" class="form-control" rows="2" required placeholder="Calle, número, ciudad..."><?= htmlspecialchars($userData['address'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success w-100 py-3 rounded-pill fw-bold fs-5 shadow">
                    <i class="bi bi-bag-check-fill me-2"></i> Confirmar Pedido
                </button>
                <div class="text-center mt-3 text-muted small">
                    <i class="bi bi-lock-fill"></i> Tus datos están protegidos con encriptación SSL.
                </div>
            </form>
        </div>

        <!-- Columna Derecha: Resumen -->
        <div class="col-md-5">
            <div class="card bg-white shadow-sm border-0 rounded-4 p-4">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-primary fw-bold">Resumen del Pedido</span>
                    <span class="badge bg-primary rounded-pill"><?= count($_SESSION['cart']) ?> items</span>
                </h4>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($_SESSION['cart'] as $id => $qty): 
                        // Nota: En un caso real deberíamos traer los nombres de la DB aquí también para mostrarlos
                        // Por simplicidad mostramos solo el total calculado arriba
                    endforeach; ?>
                    <li class="list-group-item d-flex justify-content-between lh-sm py-3">
                        <div><h6 class="my-0">Subtotal</h6></div>
                        <span class="text-muted">$<?= number_format($total, 2) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between lh-sm py-3 bg-light">
                        <div><h6 class="my-0 text-success">Envío</h6></div>
                        <span class="text-success fw-bold" id="shipping_display">$0.00</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between lh-sm py-3">
                        <div><h5 class="my-0 fw-bold">Total Final</h5></div>
                        <span class="text-dark fw-bold fs-3" id="total_display">$<?= number_format($total, 2) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script src="geo.js"></script>
    <script>
        const postalInput = document.getElementById('postal_code');
        const shippingDisplay = document.getElementById('shipping_display');
        const totalDisplay = document.getElementById('total_display');
        const shippingFeedback = document.getElementById('shipping_feedback');
        const shippingInput = document.getElementById('shipping_cost_input');
        const subtotal = <?= $total ?>;

        document.addEventListener('DOMContentLoaded', () => {
            loadProvinces('province', '<?= htmlspecialchars($userData['province'] ?? '') ?>');
        });

        function calculateShipping() {
            const cp = postalInput.value;
            
            // Lógica de envío gratis
            if (subtotal > 100000) {
                shippingDisplay.innerText = 'Gratis';
                totalDisplay.innerText = '$' + subtotal.toLocaleString();
                shippingInput.value = 0;
                shippingFeedback.innerText = "¡Envío Gratis aplicado!";
                return;
            }

            if(cp.length >= 4) {
                shippingFeedback.innerText = "Calculando envío...";
                fetch('calc_envio.php?cp=' + cp)
                    .then(res => res.json())
                    .then(data => {
                        const costo = data.costo;
                        shippingDisplay.innerText = '$' + costo.toLocaleString();
                        totalDisplay.innerText = '$' + (subtotal + costo).toLocaleString();
                        shippingInput.value = costo;
                        shippingFeedback.innerText = "Costo de envío actualizado (Andreani/Correo)";
                    });
            }
        }

        postalInput.addEventListener('blur', calculateShipping);
        // Calcular al inicio si ya hay datos (usuario logueado)
        if(postalInput.value) calculateShipping();
    </script>
</body>
</html>
