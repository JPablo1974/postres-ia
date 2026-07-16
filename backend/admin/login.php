<?php
require __DIR__ . '/_bootstrap.php';

use App\Config\Config;

if (!empty($_SESSION['admin'])) {
    header('Location: /api-postres-ai/admin/index.php');
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $user = (string) ($_POST['user'] ?? '');
    $pass = (string) ($_POST['pass'] ?? '');
    $realUser = (string) Config::get('ADMIN_USER', 'admin');
    $realPass = (string) Config::get('ADMIN_PASS', '');

    if ($realPass !== '' && hash_equals($realUser, $user) && hash_equals($realPass, $pass)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: /api-postres-ai/admin/index.php');
        exit;
    }
    $error = 'Credenciales incorrectas.';
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Acceso · Back office Ricos Postres IA</title>
<link rel="stylesheet" href="/api-postres-ai/admin/assets/admin.css">
</head>
<body class="login">
  <form method="post" class="login-card">
    <h1>Postres <em>IA</em></h1>
    <p class="muted">Panel de administración</p>
    <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    <label>Usuario<input name="user" autocomplete="username" required></label>
    <label>Contraseña<input name="pass" type="password" autocomplete="current-password" required></label>
    <button type="submit">Entrar</button>
  </form>
</body>
</html>
