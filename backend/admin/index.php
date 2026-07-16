<?php
require __DIR__ . '/_bootstrap.php';
require_login();

use App\Controllers\AdminController;

$dbError = null;
$m = ['total' => 0, 'today' => 0, 'errors24h' => 0, 'totalViews' => 0, 'top' => [], 'recent' => [], 'recentErrors' => []];

try {
    $m = (new AdminController(admin_pdo()))->metrics();
} catch (\Throwable $e) {
    $dbError = 'No se pudo conectar con la base de datos.';
}

function fecha(?string $iso): string
{
    if (!$iso) {
        return '—';
    }
    return date('d/m/Y H:i', strtotime($iso));
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Back office · Ricos Postres IA</title>
<link rel="stylesheet" href="/api-postres-ai/admin/assets/admin.css">
</head>
<body>
  <header class="topbar">
    <span class="brand">Ricos Postres <em>IA</em> · Back office</span>
    <a href="/api-postres-ai/admin/logout.php" class="logout">Salir</a>
  </header>

  <main class="wrap">
    <?php if ($dbError): ?>
      <div class="alert"><?= e($dbError) ?></div>
    <?php endif; ?>

    <section class="cards">
      <div class="card"><span class="num"><?= e($m['today']) ?></span><span class="lbl">Recetas hoy</span></div>
      <div class="card"><span class="num"><?= e($m['total']) ?></span><span class="lbl">Recetas totales</span></div>
      <div class="card"><span class="num"><?= e($m['totalViews']) ?></span><span class="lbl">Vistas totales</span></div>
      <div class="card <?= $m['errors24h'] > 0 ? 'card--alert' : '' ?>">
        <span class="num"><?= e($m['errors24h']) ?></span><span class="lbl">Errores (24 h)</span>
      </div>
    </section>

    <div class="grid">
      <section class="panel">
        <h2>Más populares</h2>
        <?php if (!$m['top']): ?>
          <p class="muted">Aún no hay recetas.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>Receta</th><th class="r">Vistas</th></tr></thead>
            <tbody>
            <?php foreach ($m['top'] as $r): ?>
              <tr><td><?= e($r['title']) ?></td><td class="r"><?= e($r['views']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>

      <section class="panel">
        <h2>Recién generadas</h2>
        <?php if (!$m['recent']): ?>
          <p class="muted">Aún no hay recetas.</p>
        <?php else: ?>
          <table>
            <thead><tr><th>Receta</th><th class="r">Fecha</th></tr></thead>
            <tbody>
            <?php foreach ($m['recent'] as $r): ?>
              <tr><td><?= e($r['title']) ?></td><td class="r"><?= fecha($r['created_at']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </section>
    </div>

    <section class="panel">
      <h2>Últimos errores</h2>
      <?php if (!$m['recentErrors']): ?>
        <p class="muted">Sin errores registrados. Todo en orden.</p>
      <?php else: ?>
        <table>
          <thead><tr><th>Origen</th><th>Mensaje</th><th class="r">Fecha</th></tr></thead>
          <tbody>
          <?php foreach ($m['recentErrors'] as $r): ?>
            <tr>
              <td><code><?= e($r['source']) ?></code></td>
              <td><?= e($r['message']) ?></td>
              <td class="r"><?= fecha($r['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
