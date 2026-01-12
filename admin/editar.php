<?php
require '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: index.php"); exit; }

// Obtener producto actual
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $ml = $_POST['ml'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category = $_POST['category'] ?? 'General';
    $description = $_POST['description'];
    $imageName = $product['image']; // Mantener imagen anterior por defecto

    // Si suben nueva imagen, reemplazarla
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $imageName);
    }

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, image=?, ml=?, category=?, stock=? WHERE id=?");
    $stmt->execute([$name, $description, $price, $imageName, $ml, $category, $stock, $id]);
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --bg-body: #f8f9fa; --text-main: #2b2d42; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-main); }
        .admin-navbar { background: white; box-shadow: 0 2px 15px rgba(0,0,0,0.04); padding: 1rem 0; }
        .brand-logo { font-weight: 800; letter-spacing: -0.5px; color: var(--primary); font-size: 1.5rem; }
        .card-custom { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); background: white; padding: 2rem; }
        .form-control, .form-select { padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid #e9ecef; background-color: #fff; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1); }
        .form-label { font-weight: 500; font-size: 0.9rem; color: #6c757d; margin-bottom: 0.5rem; }
        .btn-primary { background-color: var(--primary); border: none; padding: 0.75rem 1.5rem; border-radius: 10px; font-weight: 600; transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3); }
        .btn-light { background: #f8f9fa; border: 1px solid #e9ecef; color: #6c757d; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 10px; }
        .btn-light:hover { background: #e9ecef; color: var(--text-main); }
        .current-img-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 12px; border: 2px solid #f1f3f5; }
    </style>
</head>
<body>
    <nav class="admin-navbar mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="text-decoration-none"><span class="brand-logo"><i class="bi bi-grid-1x2-fill"></i> Dashboard</span></a>
                <span class="text-muted mx-2">/</span>
                <span class="fw-bold text-secondary">Editar Producto</span>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-light border rounded-pill px-3"><i class="bi bi-arrow-left"></i> Volver</a>
                <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-3">Salir</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card-custom">
                    <h4 class="mb-4 fw-bold">Editar: <span class="text-primary"><?= htmlspecialchars($product['name']) ?></span></h4>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4"><label class="form-label">Nombre del Producto</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required></div>
                        <div class="row">
                            <div class="col-md-6 mb-4"><label class="form-label">Precio ($)</label><input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required></div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Categoría</label>
                                <select name="category" class="form-select">
                                    <option value="General" <?= ($product['category'] ?? '') == 'General' ? 'selected' : '' ?>>General</option>
                                    <option value="Perfumes" <?= ($product['category'] ?? '') == 'Perfumes' ? 'selected' : '' ?>>Perfumes</option>
                                    <option value="Juguetes" <?= ($product['category'] ?? '') == 'Juguetes' ? 'selected' : '' ?>>Juguetes</option>
                                    <option value="Ropa" <?= ($product['category'] ?? '') == 'Ropa' ? 'selected' : '' ?>>Ropa</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-4"><label class="form-label">Stock</label><input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?? 0 ?>" required></div>
                            <div class="col-md-6 mb-4"><label class="form-label">Medida (ML)</label><input type="number" name="ml" class="form-control" value="<?= $product['ml'] ?? 0 ?>"></div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Imagen del Producto</label>
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= BASE_URL ?>uploads/<?= $product['image'] ?>" class="current-img-preview">
                                <div class="flex-grow-1">
                                    <input type="file" name="image" class="form-control">
                                    <div class="form-text">Sube una nueva imagen solo si deseas reemplazar la actual.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4"><label class="form-label">Descripción</label><textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea></div>
                        
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="index.php" class="btn btn-light">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-2"></i> Actualizar Producto</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
    </div>
</body>
</html>