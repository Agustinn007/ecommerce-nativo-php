<?php
require 'includes/config.php';

// Obtener nombre de usuario si está logueado
$userName = '';
if (isset($_SESSION['user_id'])) {
    $stmtUser = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmtUser->execute([$_SESSION['user_id']]);
    $u = $stmtUser->fetch();
    if ($u) {
        $userName = $u['name'];
    }
}

// Filtrar productos y Configurar Diseño Dinámico
$category = $_GET['category'] ?? null;
$sql = "SELECT * FROM products";
$params = [];

// Configuración por defecto (Home)
$heroTitle = "Descubre tu estilo único";
$heroDesc = "Las mejores tendencias de la temporada a un click de distancia.";
$heroStyle = "background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('uploads/chrome-hearts-logo-marking-63k7nz3vm19v7dx4.jpg') no-repeat center center; background-size: cover; color: white;";

if ($category) {
    $sql .= " WHERE category = ?";
    $params[] = $category;

    // Personalización por Categoría
    if ($category === 'Perfumes') {
        $heroTitle = "Esencias que Enamoran";
        $heroDesc = "Fragancias exclusivas para dejar huella donde vayas.";
        $heroStyle = "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;";
    } elseif ($category === 'Juguetes') {
        $heroTitle = "Diversión sin Límites";
        $heroDesc = "Explora nuestra selección de juguetes para todas las edades.";
        $heroStyle = "background: linear-gradient(120deg, #f6d365 0%, #fda085 100%); color: white;";
    } elseif ($category === 'Ropa') {
        $heroTitle = "Moda & Estilo";
        $heroDesc = "Renueva tu guardarropa con las últimas tendencias.";
        $heroStyle = "background: linear-gradient(to top, #30cfd0 0%, #330867 100%); color: white;";
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Tienda PHP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --primary-color: #667eea; --secondary-color: #764ba2; --accent-color: #ff6b6b; }
        
        /* Transiciones suaves entre páginas */
        @view-transition { navigation: auto; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; overflow-x: hidden; animation: fadeIn 0.5s ease-out; }
        
        /* Navbar Glassmorphism */
        .navbar { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px); box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; }
        .nav-link { font-weight: 500; color: #555 !important; transition: color 0.3s; }
        .nav-link:hover { color: var(--secondary-color) !important; }
        
        /* Hero Section Animado */
        .hero-section { 
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: white; padding: 100px 0; margin-bottom: 50px; border-radius: 0 0 50px 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        }
        @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        
        /* Cards & UI */
        .card { border: none; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); border-radius: 20px; overflow: hidden; background: white; }
        .card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        .card-img-top { height: 280px; object-fit: contain; padding: 20px; transition: transform 0.5s ease; }
        .card:hover .card-img-top { transform: scale(1.05); }
        
        .btn-primary { background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); border: none; padding: 10px 25px; border-radius: 50px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(118, 75, 162, 0.4); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(118, 75, 162, 0.6); filter: brightness(1.1); }
        .card .btn-primary { padding: 6px 20px; font-size: 0.9rem; } /* Botón más delgado en tarjetas */
        
        .feature-box { transition: all 0.3s; border: 1px solid rgba(0,0,0,0.05); }
        .feature-box:hover { transform: translateY(-5px); border-color: var(--primary-color); }
        .feature-icon { font-size: 2.5rem; background: -webkit-linear-gradient(45deg, var(--primary-color), var(--secondary-color)); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 1rem; }
        
        footer { background-color: #212529; color: #adb5bd; padding: 60px 0 30px; }
        footer a { color: #adb5bd; text-decoration: none; transition: color 0.3s; }
        footer a:hover { color: white; }
        
        .badge-custom { background: var(--accent-color); color: white; }
    </style>
</head>
<body>
    <!-- Navbar -->
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
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item me-2">
                                <a href="admin/index.php" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm fw-bold">
                                    <i class="bi bi-speedometer2"></i> Panel Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5"></i> Hola, <?= htmlspecialchars($userName) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                <li><a class="dropdown-item" href="perfil.php"><i class="bi bi-person-gear"></i> Mis Datos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2"><a href="login.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">Ingresar</a></li>
                        <li class="nav-item"><a href="registro.php" class="btn btn-primary btn-sm rounded-pill px-3">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section text-center" data-aos="fade-down" style="<?= $heroStyle ?>">
        <div class="container">
            <h1 class="display-3 fw-bold mb-3"><?= htmlspecialchars($heroTitle) ?></h1>
            <p class="lead mb-4 opacity-75"><?= htmlspecialchars($heroDesc) ?></p>
            <a href="#productos" class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-primary shadow-lg">Ver Colección</a>
        </div>
    </header>

    <!-- Beneficios (Para que no se vea vacía) -->
    <section class="container mb-5">
        <div class="row text-center g-4" data-aos="fade-up">
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100 feature-box">
                    <i class="bi bi-truck feature-icon"></i>
                    <h5 class="fw-bold text-dark">Envío Gratis</h5>
                    <p class="small text-muted">En compras superiores a $100.000</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100 feature-box">
                    <i class="bi bi-shield-check feature-icon"></i>
                    <h5 class="fw-bold text-dark">Compra Segura</h5>
                    <p class="small text-muted">Protegemos tus datos al 100%</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 bg-white rounded-4 shadow-sm h-100 feature-box">
                    <i class="bi bi-credit-card feature-icon"></i>
                    <h5 class="fw-bold text-dark">Cuotas sin interés</h5>
                    <p class="small text-muted">Con todas las tarjetas bancarias</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Productos -->
    <div class="container pb-5" id="productos">
        <h3 class="mb-4 fw-bold text-secondary border-start border-4 border-primary ps-3"><?= $category ? htmlspecialchars($category) : 'Novedades' ?></h3>
        <div class="row g-4">
            <?php $delay = 0; foreach ($products as $p): $delay += 100; ?>
            <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        <a href="producto.php?id=<?= $p['id'] ?>">
                            <img src="uploads/<?= $p['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
                            <?php if (($p['stock'] ?? 0) <= 0): ?>
                                <div class="position-absolute top-50 start-50 translate-middle badge bg-dark opacity-75">SIN STOCK</div>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column text-center p-3">
                        <div class="mb-1">
                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;"><?= htmlspecialchars($p['category'] ?? 'General') ?></small>
                        </div>
                        <a href="producto.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark mb-2">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($p['name']) ?></h5>
                        </a>
                        <h4 class="fw-bold text-primary mb-3">$<?= number_format($p['price'], 0) ?></h4>
                        <?php if(isset($p['ml']) && $p['ml'] > 0): ?><div class="mb-3"><span class="badge bg-light text-secondary border"><?= $p['ml'] ?> ML</span></div><?php endif; ?>
                        <div class="d-grid mt-auto">
                            <?php if (($p['stock'] ?? 0) > 0): ?>
                                <button onclick="addToCart(<?= $p['id'] ?>)" class="btn btn-primary">
                                    <i class="bi bi-cart-plus"></i> Agregar
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="bi bi-x-circle"></i> Sin Stock
                                </button>
                            <?php endif; ?>
                        </div>
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
        <!-- Aquí se carga el contenido vía AJAX -->
        <div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
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
                    // Actualizar badge
                    const badge = document.getElementById('cart-badge');
                    if(badge) {
                        badge.innerText = data.count;
                        badge.style.display = 'inline-block';
                    }
                    // Cargar y abrir el carrito lateral
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
