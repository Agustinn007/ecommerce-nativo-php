<?php
require 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>
<!-- Formulario HTML simple -->
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Login</title>
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
            <h3 class="text-center mb-4 fw-bold" style="color: #4a4a4a;">Bienvenido</h3>
            <?php if(isset($_GET['registered'])) echo "<div class='alert alert-success'>¡Cuenta creada! Ingresa ahora.</div>"; ?>
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="POST">
                <div class="mb-3"><label class="form-label text-muted small fw-bold">EMAIL</label><input type="email" name="email" class="form-control" placeholder="nombre@ejemplo.com" required></div>
                <div class="mb-3"><label class="form-label text-muted small fw-bold">CONTRASEÑA</label><input type="password" name="password" class="form-control" placeholder="••••••••" required></div>
                <button class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm mt-2">INGRESAR</button>
            </form>
            <div class="mt-4 text-center text-muted small">
                <p class="mb-2">¿No tienes cuenta? <a href="registro.php" class="text-decoration-none fw-bold">Regístrate aquí</a></p>
                <a href="index.php" class="text-decoration-none text-secondary">← Volver a la tienda</a>
            </div>
        </div>
    </div>
</body>
</html>
