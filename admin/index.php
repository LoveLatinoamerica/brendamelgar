<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// --- Login / Logout ---
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$login_error = '';
if (isset($_POST['usuario'], $_POST['password'])) {
    if ($_POST['usuario'] === ADMIN_USER && password_verify($_POST['password'], ADMIN_PASS)) {
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    }
    $login_error = 'Usuario o contraseña incorrectos';
}

if (empty($_SESSION['admin'])) {
    // --- Pantalla de login ---
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Admin — Brenda Melgar</title>
      <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;background:#faf8f6;display:flex;align-items:center;justify-content:center;min-height:100vh}
        .login{background:#fff;padding:48px;border-radius:8px;box-shadow:0 2px 20px rgba(0,0,0,.06);max-width:380px;width:100%}
        .login h1{font-family:'Cormorant Garamond',serif;font-size:1.8rem;margin-bottom:8px;color:#2c2c2c}
        .login p{color:#888;font-size:.85rem;margin-bottom:24px}
        .login label{display:block;font-size:.8rem;color:#666;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px}
        .login input{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:4px;font-size:.95rem;margin-bottom:16px;font-family:inherit}
        .login input:focus{outline:none;border-color:#D3B8AD}
        .login button{width:100%;padding:12px;background:#2c2c2c;color:#fff;border:none;border-radius:4px;font-size:.9rem;cursor:pointer;font-family:inherit}
        .login button:hover{background:#444}
        .error{color:#c0392b;font-size:.85rem;margin-bottom:12px}
      </style>
      <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    </head>
    <body>
      <form class="login" method="post">
        <h1>Administracion</h1>
        <p>Brenda Melgar</p>
        <?php if ($login_error): ?>
          <div class="error"><?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" required>
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Iniciar sesion</button>
      </form>
    </body>
    </html>
    <?php
    exit;
}

// --- Panel de administracion ---
$db = getDB();

// Visitas de hoy
$hoy = $db->query("SELECT COUNT(*) FROM visitas WHERE DATE(fecha) = CURDATE()")->fetchColumn();

// Visitas de ayer
$ayer = $db->query("SELECT COUNT(*) FROM visitas WHERE DATE(fecha) = CURDATE() - INTERVAL 1 DAY")->fetchColumn();

// Visitas este mes
$mes = $db->query("SELECT COUNT(*) FROM visitas WHERE YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())")->fetchColumn();

// Total historico
$total = $db->query("SELECT COUNT(*) FROM visitas")->fetchColumn();

// Ultimos 30 dias por dia
$por_dia = $db->query("
    SELECT DATE(fecha) AS dia, COUNT(*) AS total
    FROM visitas
    WHERE fecha >= CURDATE() - INTERVAL 30 DAY
    GROUP BY DATE(fecha)
    ORDER BY dia ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Top paginas
$top_paginas = $db->query("
    SELECT pagina, COUNT(*) AS total
    FROM visitas
    GROUP BY pagina
    ORDER BY total DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Ultimas 20 visitas
$ultimas = $db->query("
    SELECT pagina, ip, fecha, referrer
    FROM visitas
    ORDER BY fecha DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

$dias_labels = json_encode(array_column($por_dia, 'dia'));
$dias_datos = json_encode(array_map('intval', array_column($por_dia, 'total')));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Visitas — Brenda Melgar</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:#faf8f6;color:#2c2c2c;padding:24px}
    .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;flex-wrap:wrap;gap:12px}
    .header h1{font-family:'Cormorant Garamond',serif;font-size:2rem}
    .header form button{background:none;border:1px solid #ccc;padding:8px 16px;border-radius:4px;cursor:pointer;font-family:inherit;font-size:.85rem;color:#666}
    .header form button:hover{background:#eee}
    .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:32px}
    .card{background:#fff;padding:24px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
    .card__number{font-family:'Cormorant Garamond',serif;font-size:2.4rem;font-weight:500;color:#2c2c2c}
    .card__label{font-size:.75rem;color:#999;text-transform:uppercase;letter-spacing:.5px;margin-top:4px}
    .section{background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.04);padding:24px;margin-bottom:24px}
    .section h2{font-family:'Cormorant Garamond',serif;font-size:1.3rem;margin-bottom:16px}
    canvas{width:100%!important;max-height:300px}
    table{width:100%;border-collapse:collapse;font-size:.85rem}
    th{text-align:left;padding:10px 12px;border-bottom:2px solid #eee;color:#999;text-transform:uppercase;font-size:.7rem;letter-spacing:.5px}
    td{padding:10px 12px;border-bottom:1px solid #f2f2f2}
    tr:hover td{background:#faf8f6}
    .back-link{display:inline-block;margin-top:16px;color:#D3B8AD;text-decoration:none;font-size:.85rem}
    .back-link:hover{text-decoration:underline}
  </style>
</head>
<body>
  <div class="header">
    <h1>Panel de Administracion</h1>
    <div style="display:flex;gap:12px;align-items:center">
      <a href="../index.php" class="back-link">Ver sitio</a>
      <form method="post" style="margin:0"><button type="submit" name="logout" value="1">Cerrar sesion</button></form>
    </div>
  </div>

  <div class="cards">
    <div class="card">
      <div class="card__number"><?= number_format($hoy) ?></div>
      <div class="card__label">Visitas hoy</div>
    </div>
    <div class="card">
      <div class="card__number"><?= number_format($ayer) ?></div>
      <div class="card__label">Visitas ayer</div>
    </div>
    <div class="card">
      <div class="card__number"><?= number_format($mes) ?></div>
      <div class="card__label">Este mes</div>
    </div>
    <div class="card">
      <div class="card__number"><?= number_format($total) ?></div>
      <div class="card__label">Total historico</div>
    </div>
  </div>

  <div class="section">
    <h2>Visitas - Ultimos 30 dias</h2>
    <canvas id="chart"></canvas>
  </div>

  <div class="section">
    <h2>Paginas mas visitadas</h2>
    <table>
      <thead><tr><th>Pagina</th><th>Visitas</th></tr></thead>
      <tbody>
        <?php foreach ($top_paginas as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['pagina']) ?></td>
          <td><?= number_format($p['total']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="section">
    <h2>Ultimas visitas</h2>
    <table>
      <thead><tr><th>Pagina</th><th>IP</th><th>Referrer</th><th>Fecha</th></tr></thead>
      <tbody>
        <?php foreach ($ultimas as $v): ?>
        <tr>
          <td><?= htmlspecialchars($v['pagina']) ?></td>
          <td><?= htmlspecialchars($v['ip']) ?></td>
          <td><?= htmlspecialchars($v['referrer'] ?: '-') ?></td>
          <td><?= $v['fecha'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
  <script>
    new Chart(document.getElementById('chart'), {
      type: 'bar',
      data: {
        labels: <?= $dias_labels ?>,
        datasets: [{
          label: 'Visitas',
          data: <?= $dias_datos ?>,
          backgroundColor: 'rgba(211,184,173,0.5)',
          borderColor: '#D3B8AD',
          borderWidth: 1,
          borderRadius: 4
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1 } },
          x: { ticks: { maxRotation: 45, font: { size: 10 } } }
        }
      }
    });
  </script>
</body>
</html>
