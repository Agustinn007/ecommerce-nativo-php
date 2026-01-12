<?php
require 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit;
}

$userId = $_SESSION['user_id'];
$msg = '';

// Actualizar datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $lastname = $_POST['lastname'];
    $dni = $_POST['dni'];
    $phone = $_POST['phone'];
    $postal_code = $_POST['postal_code'];
    $address = $_POST['address'];
    $province = $_POST['province'] ?? '';
    $city = $_POST['city'] ?? '';

    $sql = "UPDATE users SET name=?, lastname=?, dni=?, phone=?, postal_code=?, address=?, province=?, city=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    if($stmt->execute([$name, $lastname, $dni, $phone, $postal_code, $address, $province, $city, $userId])) {
        $msg = "Datos actualizados correctamente.";
    }
}

// Obtener datos actuales
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Mis Datos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }</style>
</head>
<body class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 p-4">
                <h3 class="mb-4 fw-bold">Mis Datos Personales</h3>
                <?php if($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Nombre</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Apellido</label><input type="text" name="lastname" class="form-control" value="<?= htmlspecialchars($user['lastname'] ?? '') ?>"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">DNI</label><input type="text" name="dni" class="form-control" value="<?= htmlspecialchars($user['dni'] ?? '') ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Teléfono</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Provincia</label>
                            <select name="province" id="province" class="form-select" data-city-target="city" data-city-value="<?= htmlspecialchars($user['city'] ?? '') ?>" required></select>
                        </div>
                        <div class="col-md-6 mb-3"><label class="form-label">Ciudad</label>
                            <select name="city" id="city" class="form-select" required><option value="">Selecciona provincia primero</option></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Código Postal</label><input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>"></div>
                        <div class="col-md-8 mb-3"><label class="form-label">Dirección</label><input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="index.php" class="btn btn-outline-secondary">Volver</a>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="geo.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadProvinces('province', '<?= htmlspecialchars($user['province'] ?? '') ?>');
        });
    </script>
</body>
</html>
