<!DOCTYPE html>
<html lang="es">
<head>
    <title>¡Gracias!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .success-card { background: white; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); max-width: 500px; width: 100%; text-align: center; padding: 50px; animation: popIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55); }
        .icon-circle { width: 100px; height: 100px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #28a745; font-size: 3rem; }
        @keyframes popIn { 0% { transform: scale(0.5); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .btn-primary { background: linear-gradient(45deg, #667eea, #764ba2); border: none; padding: 12px 30px; border-radius: 50px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-card mx-auto">
            <div class="icon-circle">
                <i class="bi bi-check-lg"></i>
            </div>
            <h1 class="fw-bold mb-3">¡Gracias por tu compra!</h1>
            <p class="text-muted mb-4">Tu pedido ha sido procesado con éxito. Te hemos enviado un correo electrónico con los detalles de tu orden.</p>
            
            <div class="d-grid gap-2">
                <a href="index.php" class="btn btn-primary fw-bold shadow">
                    <i class="bi bi-arrow-left"></i> Volver a la Tienda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
