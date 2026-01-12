<?php
require '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $ml = $_POST['ml'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category = $_POST['category'] ?? 'General';
    $description = $_POST['description'];
    
    // Manejo de imagen
    $imageName = time() . '_' . $_FILES['image']['name'];
    $target = '../uploads/' . $imageName;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, ml, category, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $imageName, $ml, $category, $stock]);
        header("Location: index.php");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Agregar Producto</title>
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
    </style>
</head>
<body>
    <nav class="admin-navbar mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="text-decoration-none"><span class="brand-logo"><i class="bi bi-grid-1x2-fill"></i> Dashboard</span></a>
                <span class="text-muted mx-2">/</span>
                <span class="fw-bold text-secondary">Nuevo Producto</span>
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
                    <h4 class="mb-4 fw-bold">Información del Producto</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label">Nombre del Producto</label>
                            <input type="text" name="name" class="form-control" placeholder="Ej: Perfume Ocean Blue" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Precio ($)</label>
                                <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Categoría</label>
                                <select name="category" class="form-select">
                                    <option value="General">General</option>
                                    <option value="Perfumes">Perfumes</option>
                                    <option value="Juguetes">Juguetes</option>
                                    <option value="Ropa">Ropa</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-4"><label class="form-label">Stock Disponible</label><input type="number" name="stock" class="form-control" required placeholder="0"></div>
                            <div class="col-md-6 mb-4"><label class="form-label">Medida (ML/Talle)</label><input type="number" name="ml" class="form-control" placeholder="Ej: 100"></div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Imagen Principal</label>
                            <input type="file" name="image" class="form-control" required>
                            <div class="form-text">Formatos aceptados: JPG, PNG, WEBP.</div>
                        </div>
                        
                        <div class="mb-4 p-3 bg-light rounded-3 border border-dashed">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label m-0 fw-bold text-primary"><i class="bi bi-stars"></i> Descripción con IA</label>
                                <button type="button" class="btn btn-sm btn-white border shadow-sm text-primary" id="btn-ia"><i class="bi bi-magic"></i> Generar</button>
                            </div>
                            <input type="text" id="ia-details" class="form-control mb-2 border-0 shadow-none bg-white" placeholder="Escribe detalles clave (ej: perfume floral, elegante)...">
                            <textarea name="description" id="description" class="form-control border-0 shadow-none bg-white" rows="4" placeholder="La descripción se generará aquí..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="index.php" class="btn btn-light">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-2"></i> Guardar Producto</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
    </div>
    
    <script>
        // Script IA (Mantenido)
        document.getElementById('btn-ia').addEventListener('click', function() {
            const name = document.querySelector('input[name="name"]').value;
            const details = document.getElementById('ia-details').value;
            const btn = this;
            
            if(!name) { alert('Por favor escribe el nombre del producto primero.'); return; }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch('generar_ia.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ name: name, details: details })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('description').value = data.description;
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-magic"></i> Generar';
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Reintentar';
            });
        });
    </script>
</body>
</html>
