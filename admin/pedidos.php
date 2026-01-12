<?php
require '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}

// Eliminar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order_id'])) {
    $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$_POST['delete_order_id']]);
    $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$_POST['delete_order_id']]);
    header("Location: pedidos.php"); exit;
}

// Actualizar estado si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    header("Location: pedidos.php"); exit;
}

// Obtener órdenes con información del usuario (si existe)
$sql = "SELECT o.*, u.name as user_name, u.email as user_email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cálculos extra para el dashboard de pedidos
$totalOrders = count($orders);
$pendingOrders = count(array_filter($orders, function($o) { return ($o['status'] ?? 'pending') == 'pending'; }));
$completedOrders = count(array_filter($orders, function($o) { return ($o['status'] ?? '') == 'delivered'; }));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Administrar Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --bg-body: #f8f9fa; --text-main: #2b2d42; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-main); }
        
        .admin-navbar { background: white; box-shadow: 0 2px 15px rgba(0,0,0,0.04); padding: 1rem 0; }
        .brand-logo { font-weight: 800; letter-spacing: -0.5px; color: var(--primary); font-size: 1.5rem; }
        
        .table-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); overflow: hidden; padding: 1.5rem; }
        .table thead th { background: #f8f9fa; color: #6c757d; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; border: none; padding: 1rem; }
        .table tbody td { padding: 1rem; vertical-align: middle; border-bottom: 1px solid #f1f3f5; }
        
        /* Status Select Personalizado */
        .status-select { 
            border: none; 
            padding: 0.4rem 1rem; 
            border-radius: 30px; 
            font-weight: 600; 
            font-size: 0.85rem; 
            cursor: pointer; 
            appearance: none; 
            background-image: none;
            text-align: center;
            transition: all 0.2s;
        }
        .status-select:hover { filter: brightness(0.95); }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-shipped { background-color: #cff4fc; color: #055160; }
        .status-delivered { background-color: #d1e7dd; color: #0f5132; }
        .status-cancelled { background-color: #f8d7da; color: #842029; }

        .mini-stat { background: white; border-radius: 12px; padding: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 1rem; }
        .mini-stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    </style>
</head>
<body>
    <nav class="admin-navbar mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="text-decoration-none"><span class="brand-logo"><i class="bi bi-grid-1x2-fill"></i> Dashboard</span></a>
                <span class="text-muted mx-2">/</span>
                <span class="fw-bold text-secondary">Pedidos</span>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-light border rounded-pill px-3"><i class="bi bi-arrow-left"></i> Volver</a>
                <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-3">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Resumen Superior (Extra) -->
        <div class="row mb-4 g-3">
            <div class="col-md-4">
                <div class="mini-stat">
                    <div class="mini-stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-receipt"></i></div>
                    <div><h6 class="m-0 text-muted small">TOTAL PEDIDOS</h6><h4 class="m-0 fw-bold"><?= $totalOrders ?></h4></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stat">
                    <div class="mini-stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div>
                    <div><h6 class="m-0 text-muted small">PENDIENTES</h6><h4 class="m-0 fw-bold"><?= $pendingOrders ?></h4></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stat">
                    <div class="mini-stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i></div>
                    <div><h6 class="m-0 text-muted small">COMPLETADOS</h6><h4 class="m-0 fw-bold"><?= $completedOrders ?></h4></div>
                </div>
            </div>
        </div>
        
        <div class="table-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0">Transacciones Recientes</h5>
                <div class="input-group w-auto">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 bg-light" placeholder="Buscar pedido..." id="searchInput">
                </div>
            </div>

            <div class="table-responsive">
            <table class="table table-hover align-middle" id="ordersTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#ID</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <?php 
                        $statusClass = 'status-pending';
                        if(($o['status']??'') == 'shipped') $statusClass = 'status-shipped';
                        if(($o['status']??'') == 'delivered') $statusClass = 'status-delivered';
                        if(($o['status']??'') == 'cancelled') $statusClass = 'status-cancelled';
                    ?>
                    <tr>
                        <td class="ps-3 fw-bold text-primary">#<?= $o['id'] ?></td>
                        <td class="text-muted small"><?= date('d M, Y', strtotime($o['created_at'])) ?><br><?= date('H:i', strtotime($o['created_at'])) ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($o['guest_name'] ?? $o['user_name']) ?></div>
                            <div class="small text-muted"><i class="bi bi-envelope"></i> <?= htmlspecialchars($o['guest_email'] ?? $o['user_email']) ?></div>
                        </td>
                        <td class="fw-bold">$<?= number_format($o['total'], 2) ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="status" class="status-select <?= $statusClass ?>" onchange="this.form.submit()">
                                    <option value="pending" <?= (!isset($o['status']) || $o['status'] == 'pending') ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="shipped" <?= (isset($o['status']) && $o['status'] == 'shipped') ? 'selected' : '' ?>>Enviado</option>
                                    <option value="delivered" <?= (isset($o['status']) && $o['status'] == 'delivered') ? 'selected' : '' ?>>Entregado</option>
                                    <option value="cancelled" <?= (isset($o['status']) && $o['status'] == 'cancelled') ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                            </form>
                        </td>
                        <td class="text-end pe-3">
                            <button type="button" class="btn btn-sm btn-light text-primary me-1" data-bs-toggle="modal" data-bs-target="#orderModal<?= $o['id'] ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            <form method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este pedido?');">
                                <input type="hidden" name="delete_order_id" value="<?= $o['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </form>

                            <!-- Modal Detalles (Extra) -->
                            <div class="modal fade text-start" id="orderModal<?= $o['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4 border-0">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title fw-bold">Pedido #<?= $o['id'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted small mb-3">Dirección de envío:</p>
                                            <div class="alert alert-light border mb-3">
                                                <i class="bi bi-geo-alt text-danger"></i> <?= htmlspecialchars($o['guest_address']) ?>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between fw-bold">
                                                <span>Total Pagado:</span>
                                                <span class="text-success">$<?= number_format($o['total'], 2) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Buscador simple en tiempo real (Extra)
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#ordersTable tbody tr');
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>