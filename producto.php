<?php
require 'includes/config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) { header("Location: index.php"); exit; }

// Obtener productos relacionados (aleatorios, excluyendo el actual)
$stmtRelated = $pdo->prepare("SELECT * FROM products WHERE id != ? ORDER BY RAND() LIMIT 4");
$stmtRelated->execute([$id]);
$related = $stmtRelated->fetchAll(PDO::FETCH_ASSOC);

// Obtener nombre de usuario para el navbar
$userName = '';
if (isset($_SESSION['user_id'])) {
    $stmtUser = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmtUser->execute([$_SESSION['user_id']]);
    $u = $stmtUser->fetch();
    if ($u) $userName = $u['name'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($p['name']) ?> - Mi Tienda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #667eea; --secondary-color: #764ba2; --accent-color: #ff6b6b; }
        
        @view-transition { navigation: auto; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; animation: fadeIn 0.5s ease-out; }
        .navbar { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px); box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05); }
        .btn-primary { background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); border: none; padding: 10px 25px; border-radius: 50px; transition: all 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.1); }
        .product-img { max-height: 400px; object-fit: contain; width: 100%; border-radius: 20px; background: white; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .badge-custom { background: var(--accent-color); color: white; }
        footer { background-color: #212529; color: #adb5bd; padding: 60px 0 30px; margin-top: 80px; }
        footer a { color: #adb5bd; text-decoration: none; transition: color 0.3s; }
        footer a:hover { color: white; }
    </style>
</head>
<body>
    <!-- Navbar (Mismo que index) -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php"><i class="bi bi-bag-heart-fill"></i> MI TIENDA</a>
            
            <!-- Acciones (Carrito + Toggler) siempre visibles -->
            <div class="d-flex align-items-center order-lg-2">
                <a href="#" class="nav-link position-relative me-3 me-lg-4" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas" onclick="loadCart()">
                    <i class="bi bi-cart3 fs-4"></i>
                    <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill badge-custom" style="<?= (!isset($_SESSION['cart']) || count($_SESSION['cart']) == 0) ? 'display:none;' : '' ?>">
                        <?= array_sum($_SESSION['cart'] ?? []) ?>
                    </span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            </div>

            <div class="collapse navbar-collapse order-lg-1" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Categorías
                        </a>
                        <ul class="dropdown-menu border-0 shadow">
                            <li><a class="dropdown-item" href="index.php?category=Perfumes">Perfumes</a></li>
                            <li><a class="dropdown-item" href="index.php?category=Juguetes">Juguetes</a></li>
                            <li><a class="dropdown-item" href="index.php?category=Ropa">Ropa</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php">Ver Todo</a></li>
                        </ul>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="bi bi-person-circle fs-5"></i> Hola, <?= htmlspecialchars($userName) ?></a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                <li><a class="dropdown-item" href="perfil.php">Mis Datos</a></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Salir</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2"><a href="login.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">Ingresar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb (Navegación) -->
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($p['name']) ?></li>
            </ol>
        </nav>
    </div>

    <!-- Detalle del Producto -->
    <div class="container mt-3">
        <?php if (($p['stock'] ?? 0) <= 0): ?>
            <div class="alert alert-danger text-center fw-bold mb-4 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> ESTE PRODUCTO SE ENCUENTRA SIN STOCK
            </div>
        <?php endif; ?>
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <img src="uploads/<?= $p['image'] ?>" class="product-img" alt="<?= htmlspecialchars($p['name']) ?>">
            </div>
            <div class="col-md-6">
                <h1 class="display-4 fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></h1>
                
                <div class="d-flex align-items-center mb-3">
                    <h2 class="text-primary fw-bold mb-0 me-3">$<?= number_format($p['price'], 2) ?></h2>
                    <?php if(isset($p['ml']) && $p['ml'] > 0): ?>
                        <span class="badge bg-secondary fs-6 rounded-pill"><?= $p['ml'] ?> ML</span>
                    <?php endif; ?>
                </div>

                <div class="mb-4 p-3 bg-light rounded-3 border-start border-4 border-primary">
                    <h6 class="fw-bold text-dark mb-2">Descripción</h6>
                    <p class="text-muted m-0" style="line-height: 1.7; white-space: pre-line;"><?= htmlspecialchars($p['description']) ?></p>
                </div>

                <div class="d-grid gap-2 d-md-block">
                    <?php if (($p['stock'] ?? 0) > 0): ?>
                        <button onclick="addToCart(<?= $p['id'] ?>)" class="btn btn-primary btn-lg px-5 shadow">
                            <i class="bi bi-cart-plus"></i> Agregar al Carrito
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg px-5 shadow" disabled>
                            <i class="bi bi-x-circle"></i> Sin Stock
                        </button>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-outline-secondary btn-lg px-4">Volver</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos Relacionados -->
    <div class="container mt-5 mb-5">
        <h3 class="mb-4 fw-bold text-secondary border-start border-4 border-primary ps-3">También te puede interesar</h3>
        <div class="row g-4">
            <?php foreach ($related as $rp): ?>
            <div class="col-6 col-md-3">
                <div class="card h-100 shadow-sm border-0">
                    <a href="producto.php?id=<?= $rp['id'] ?>">
                        <div class="position-relative">
                            <img src="uploads/<?= $rp['image'] ?>" class="card-img-top p-3" style="height: 180px; object-fit: contain;" alt="<?= htmlspecialchars($rp['name']) ?>">
                            <?php if (($rp['stock'] ?? 0) == 0): ?>
                                <div class="position-absolute top-50 start-50 translate-middle badge bg-dark opacity-75">SIN STOCK</div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="card-body p-3">
                        <a href="producto.php?id=<?= $rp['id'] ?>" class="text-decoration-none text-dark">
                            <h6 class="card-title fw-bold text-truncate"><?= htmlspecialchars($rp['name']) ?></h6>
                        </a>
                        <p class="text-primary fw-bold mb-2">$<?= number_format($rp['price'], 0) ?></p>
                        <a href="producto.php?id=<?= $rp['id'] ?>" class="btn btn-sm btn-outline-primary w-100 rounded-pill">Ver</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-auto">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-5 col-md-6">
                    <h5 class="text-white mb-3"><i class="bi bi-bag-heart-fill text-primary"></i> MI TIENDA</h5>
                    <p class="small">Tu destino número uno para la moda y el estilo. Calidad garantizada en cada prenda.</p>
                    <div class="d-flex gap-3">
                        <a href="https://instagram.com" target="_blank"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="https://facebook.com" target="_blank"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="https://twitter.com" target="_blank"><i class="bi bi-twitter fs-5"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white mb-3">Navegación</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="index.php">Inicio</a></li>
                        <li class="mb-2"><a href="index.php#productos">Catálogo</a></li>
                        <li class="mb-2"><a href="carrito.php">Mi Carrito</a></li>
                        <li class="mb-2"><a href="<?= isset($_SESSION['user_id']) ? 'perfil.php' : 'login.php' ?>">Mi Cuenta</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="text-white mb-3">Contacto</h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i> Buenos Aires, Argentina</li>
                        <li class="mb-2"><i class="bi bi-envelope text-primary me-2"></i> <a href="mailto:contacto@mitienda.com">contacto@mitienda.com</a></li>
                        <li class="mb-2"><i class="bi bi-whatsapp text-primary me-2"></i> <a href="https://wa.me/" target="_blank">WhatsApp</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-4 text-center small">
                &copy; 2026 Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- Cart Offcanvas (Ventana Lateral) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold" id="cartOffcanvasLabel">Tu Carrito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body" id="cart-offcanvas-body">
        <div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadCart() {
            fetch('ajax/get_cart_html.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('cart-offcanvas-body').innerHTML = html;
                });
        }

        function addToCart(id) {
            fetch('ajax/ajax_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const badge = document.getElementById('cart-badge');
                    if(badge) {
                        badge.innerText = data.count;
                        badge.style.display = 'inline-block';
                    }
                    loadCart();
                    var myOffcanvas = new bootstrap.Offcanvas(document.getElementById('cartOffcanvas'));
                    myOffcanvas.show();
                } else {
                    alert(data.message);
                }
            });
        }
    </script>
</body>
</html>