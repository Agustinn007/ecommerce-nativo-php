<?php
require 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$name, $email, $password]);
        header("Location: login.php?registered=1");
        exit;
    } catch (PDOException $e) {
        $error = "Este email ya está registrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Registrarse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); animation: fadeIn 0.8s ease; background: rgba(255,255,255,0.95); }
        .btn-primary { background: linear-gradient(45deg, #667eea, #764ba2); border: none; transition: transform 0.2s; }
        .btn-primary:hover { transform: scale(1.02); }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid #eee; background: #f9f9f9; }
        .form-control:focus { box-shadow: none; border-color: #764ba2; background: white; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="card-body">
            <h3 class="text-center mb-4 fw-bold" style="color: #4a4a4a;">Crear Cuenta</h3>
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST">
                <div class="mb-3"><label class="form-label text-muted small fw-bold">NOMBRE</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label text-muted small fw-bold">EMAIL</label><input type="email" name="email" class="form-control" required></div>
                <div class="mb-3"><label class="form-label text-muted small fw-bold">CONTRASEÑA</label><input type="password" name="password" class="form-control" required></div>
                <button class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm mt-2">REGISTRARSE</button>
            </form>
            <div class="mt-4 text-center text-muted small">
                ¿Ya tienes cuenta? <a href="login.php" class="text-decoration-none fw-bold" style="color: #764ba2;">Inicia Sesión</a>
            </div>
        </div>
    </div>
</body>
</html>