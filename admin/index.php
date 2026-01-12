<?php
require '../includes/config.php';

// Verificar si es admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Eliminar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product) {
        try {
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            // Solo eliminar imagen si se borró de la BD exitosamente
            if (!empty($product['image']) && file_exists("../uploads/" . $product['image'])) {
                unlink("../uploads/" . $product['image']);
            }
        } catch (PDOException $e) {
            // Error de integridad (FK) - El producto está en pedidos
            header("Location: index.php?error=constraint");
            exit;
        }
    }
    header("Location: index.php");
    exit;
}

// Estadísticas para el Dashboard
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total) FROM orders")->fetchColumn();

// Obtener productos
// Filtro de categoría
$categoryFilter = $_GET['category'] ?? 'all';
$sql = "SELECT * FROM products";
if ($categoryFilter !== 'all') {
    $sql .= " WHERE category = :category";
}
$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
if ($categoryFilter !== 'all') $stmt->bindParam(':category', $categoryFilter);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Productos con stock bajo (Extra)
$lowStock = array_filter($products, function($p) { return ($p['stock'] ?? 0) < 5; });
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --secondary: #3f37c9; --success: #4cc9f0; --bg-body: #f8f9fa; --text-main: #2b2d42; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-main); }
        
        /* Navbar Moderno */
        .admin-navbar { background: white; box-shadow: 0 2px 15px rgba(0,0,0,0.04); padding: 1rem 0; }
        .brand-logo { font-weight: 800; letter-spacing: -0.5px; color: var(--primary); font-size: 1.5rem; }
        
        /* Cards Stats */
        .stat-card { border: none; border-radius: 16px; background: white; padding: 1.5rem; transition: all 0.3s ease; box-shadow: 0 4px 20px rgba(0,0,0,0.02); position: relative; overflow: hidden; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.05); }
        .stat-icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; }
        .stat-value { font-size: 2rem; font-weight: 700; margin-bottom: 0; line-height: 1; }
        .stat-label { color: #8d99ae; font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Tabla Moderna */
        .table-container { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); padding: 1.5rem; }
        .table thead th { background: #f8f9fa; color: #6c757d; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; border: none; padding: 1rem; }
        .table tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f3f5; color: #495057; font-weight: 500; }
        .table tbody tr:last-child td { border-bottom: none; }
        .product-img-thumb { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        /* Botones y Badges */
        .btn-primary-soft { background: rgba(67, 97, 238, 0.1); color: var(--primary); border: none; font-weight: 600; }
        .btn-primary-soft:hover { background: var(--primary); color: white; }
        .badge-stock { padding: 6px 12px; border-radius: 30px; font-weight: 600; font-size: 0.75rem; }
        
        /* Filtros */
        .filter-pill { border: 1px solid #e9ecef; color: #6c757d; border-radius: 30px; padding: 0.5rem 1.2rem; font-weight: 500; transition: all 0.2s; text-decoration: none; display: inline-block; }
        .filter-pill:hover, .filter-pill.active { background: var(--text-main); color: white; border-color: var(--text-main); }
    </style>
</head>
<body>
    <nav class="admin-navbar mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="brand-logo"><i class="bi bi-grid-1x2-fill"></i> Dashboard</span>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-2 ms-2">v2.0</span>
            </div>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-light border rounded-pill px-3"><i class="bi bi-arrow-up-right"></i> Ver Tienda</a>
                <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-3">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Tarjetas de Estadísticas -->
        <div class="row mb-5 g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon-box bg-success bg-opacity-10 text-success"><i class="bi bi-wallet2"></i></div>
                    <h6 class="stat-label">Ingresos Totales</h6>
                    <h2 class="stat-value">$<?= number_format($totalRevenue ?? 0, 2) ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <a href="pedidos.php" class="text-decoration-none">
                    <div class="stat-card">
                        <div class="stat-icon-box bg-primary bg-opacity-10 text-primary"><i class="bi bi-bag-check-fill"></i></div>
                        <h6 class="stat-label">Pedidos Totales</h6>
                        <h2 class="stat-value"><?= $totalOrders ?></h2>
                        <div class="position-absolute top-0 end-0 m-3 text-primary"><i class="bi bi-arrow-right"></i></div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon-box bg-warning bg-opacity-10 text-warning"><i class="bi bi-box-seam-fill"></i></div>
                    <h6 class="stat-label">Inventario Activo</h6>
                    <h2 class="stat-value"><?= $totalProducts ?></h2>
                </div>
            </div>
        </div>

        <!-- Alerta Stock Bajo (Extra) -->
        <?php if(!empty($lowStock)): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>Atención:</strong> Hay <?= count($lowStock) ?> productos con stock bajo (menos de 5 unidades).
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Sidebar de Acciones Rápidas (Extra) -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm rounded-4 p-3">
                    <h6 class="fw-bold text-muted mb-3 ps-2">ACCIONES RÁPIDAS</h6>
                    <div class="d-grid gap-2">
                        <a href="agregar.php" class="btn btn-primary-soft text-start py-2 rounded-3"><i class="bi bi-plus-circle me-2"></i> Nuevo Producto</a>
                        <a href="pedidos.php" class="btn btn-light text-start py-2 rounded-3 text-muted"><i class="bi bi-list-check me-2"></i> Gestionar Pedidos</a>
                        <a href="../index.php" class="btn btn-light text-start py-2 rounded-3 text-muted"><i class="bi bi-eye me-2"></i> Ver Tienda como Cliente</a>
                    </div>
                    
                    <h6 class="fw-bold text-muted mt-4 mb-3 ps-2">FILTRAR POR</h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="?category=all" class="filter-pill <?= $categoryFilter == 'all' ? 'active' : '' ?>">Todos</a>
                        <a href="?category=Perfumes" class="filter-pill <?= $categoryFilter == 'Perfumes' ? 'active' : '' ?>">Perfumes</a>
                        <a href="?category=Juguetes" class="filter-pill <?= $categoryFilter == 'Juguetes' ? 'active' : '' ?>">Juguetes</a>
                        <a href="?category=Ropa" class="filter-pill <?= $categoryFilter == 'Ropa' ? 'active' : '' ?>">Ropa</a>
                    </div>
                </div>
            </div>

            <!-- Tabla Principal -->
            <div class="col-lg-9">
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0">Inventario</h5>
                        <span class="text-muted small"><?= count($products) ?> items encontrados</span>
                    </div>

                    <?php if (isset($_GET['error']) && $_GET['error'] == 'constraint'): ?>
                        <div class="alert alert-danger rounded-3 mb-3">
                            <i class="bi bi-x-circle-fill me-2"></i> No se puede eliminar: el producto está en pedidos existentes.
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                    <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Producto</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <img src="<?= BASE_URL ?>uploads/<?= $p['image'] ?>" class="product-img-thumb me-3">
                                <span class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></span>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-secondary border fw-normal"><?= htmlspecialchars($p['category'] ?? 'General') ?></span></td>
                        <td>
                            <?php $stk = $p['stock'] ?? 0; ?>
                            <span class="badge-stock <?= $stk == 0 ? 'bg-danger bg-opacity-10 text-danger' : ($stk < 5 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success') ?>">
                                <?= $stk ?> unid.
                            </span>
                        </td>
                        <td class="fw-bold text-dark">$<?= number_format($p['price'], 2) ?></td>
                        <td class="text-end pe-3">
                            <a href="editar.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-light text-primary me-1"><i class="bi bi-pencil-square"></i></a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
