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

// --- Cambio de estado ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $tabla = $_POST['tabla'] === 'speaking' ? 'speaking' : 'contacto';
    $id = intval($_POST['id']);
    $estado = $_POST['estado'];
    $estados_validos = ['nuevo', 'contactado', 'aceptado', 'descartado'];
    if (in_array($estado, $estados_validos) && $id > 0) {
        $stmt = getDB()->prepare("UPDATE $tabla SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
    }
    header('Location: index.php?tab=' . urlencode($_POST['tab'] ?? 'visitas'));
    exit;
}

// --- Tab activa ---
$tab = $_GET['tab'] ?? 'visitas';

$db = getDB();

// --- Datos visitas ---
$hoy = $db->query("SELECT COUNT(*) FROM visitas WHERE DATE(fecha) = CURDATE()")->fetchColumn();
$ayer = $db->query("SELECT COUNT(*) FROM visitas WHERE DATE(fecha) = CURDATE() - INTERVAL 1 DAY")->fetchColumn();
$mes = $db->query("SELECT COUNT(*) FROM visitas WHERE YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())")->fetchColumn();
$total_visitas = $db->query("SELECT COUNT(*) FROM visitas")->fetchColumn();

$por_dia = $db->query("
    SELECT DATE(fecha) AS dia, COUNT(*) AS total
    FROM visitas
    WHERE fecha >= CURDATE() - INTERVAL 30 DAY
    GROUP BY DATE(fecha)
    ORDER BY dia ASC
")->fetchAll(PDO::FETCH_ASSOC);

$ultimas = $db->query("
    SELECT pagina, ip, fecha, referrer
    FROM visitas
    ORDER BY fecha DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);

$dias_labels = json_encode(array_column($por_dia, 'dia'));
$dias_datos = json_encode(array_map('intval', array_column($por_dia, 'total')));

// --- Datos contacto ---
$filtro_contacto = $_GET['estado_contacto'] ?? '';
$sql_contacto = "SELECT * FROM contacto";
if ($filtro_contacto && in_array($filtro_contacto, ['nuevo','contactado','aceptado','descartado'])) {
    $sql_contacto .= " WHERE estado = " . $db->quote($filtro_contacto);
}
$sql_contacto .= " ORDER BY fecha DESC";
$contactos = $db->query($sql_contacto)->fetchAll(PDO::FETCH_ASSOC);

$contacto_nuevos = $db->query("SELECT COUNT(*) FROM contacto WHERE estado = 'nuevo'")->fetchColumn();
$contacto_total = $db->query("SELECT COUNT(*) FROM contacto")->fetchColumn();

// --- Datos speaking ---
$filtro_speaking = $_GET['estado_speaking'] ?? '';
$sql_speaking = "SELECT * FROM speaking";
if ($filtro_speaking && in_array($filtro_speaking, ['nuevo','contactado','aceptado','descartado'])) {
    $sql_speaking .= " WHERE estado = " . $db->quote($filtro_speaking);
}
$sql_speaking .= " ORDER BY fecha DESC";
$speakings = $db->query($sql_speaking)->fetchAll(PDO::FETCH_ASSOC);

$speaking_nuevos = $db->query("SELECT COUNT(*) FROM speaking WHERE estado = 'nuevo'")->fetchColumn();
$speaking_total = $db->query("SELECT COUNT(*) FROM speaking")->fetchColumn();

// --- Datos descargas ---
$descargas = $db->query("SELECT * FROM descargas ORDER BY fecha DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
$descargas_total = $db->query("SELECT COUNT(*) FROM descargas")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Brenda Melgar</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:#faf8f6;color:#2c2c2c;padding:24px;max-width:1200px;margin:0 auto}
    .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px}
    .header h1{font-family:'Cormorant Garamond',serif;font-size:2rem}
    .header form button{background:none;border:1px solid #ccc;padding:8px 16px;border-radius:4px;cursor:pointer;font-family:inherit;font-size:.85rem;color:#666}
    .header form button:hover{background:#eee}
    .back-link{color:#D3B8AD;text-decoration:none;font-size:.85rem}
    .back-link:hover{text-decoration:underline}

    /* Tabs */
    .tabs{display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid #eee}
    .tab{padding:12px 24px;font-size:.8rem;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:#999;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .3s}
    .tab:hover{color:#2c2c2c}
    .tab.active{color:#2c2c2c;border-bottom-color:#D3B8AD}
    .tab .badge{display:inline-block;background:#D3B8AD;color:#fff;font-size:.65rem;padding:2px 7px;border-radius:10px;margin-left:6px;vertical-align:middle}

    /* Cards */
    .cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px}
    .card{background:#fff;padding:24px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.04)}
    .card__number{font-family:'Cormorant Garamond',serif;font-size:2.4rem;font-weight:500;color:#2c2c2c}
    .card__label{font-size:.75rem;color:#999;text-transform:uppercase;letter-spacing:.5px;margin-top:4px}

    /* Section */
    .section{background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.04);padding:24px;margin-bottom:24px}
    .section h2{font-family:'Cormorant Garamond',serif;font-size:1.3rem;margin-bottom:16px}
    canvas{width:100%!important;max-height:300px}

    /* Table */
    .table-wrap{overflow-x:auto}
    table{width:100%;border-collapse:collapse;font-size:.82rem}
    th{text-align:left;padding:10px 12px;border-bottom:2px solid #eee;color:#999;text-transform:uppercase;font-size:.7rem;letter-spacing:.5px;white-space:nowrap}
    td{padding:10px 12px;border-bottom:1px solid #f2f2f2;vertical-align:top}
    tr:hover td{background:#faf8f6}

    /* Estado badges */
    .estado{display:inline-block;padding:3px 10px;border-radius:12px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.3px}
    .estado--nuevo{background:#e8f4fd;color:#2980b9}
    .estado--contactado{background:#fef3e2;color:#e67e22}
    .estado--aceptado{background:#e8f8e8;color:#27ae60}
    .estado--descartado{background:#fde8e8;color:#c0392b}

    /* Estado select */
    .estado-select{font-family:inherit;font-size:.75rem;padding:4px 8px;border:1px solid #ddd;border-radius:4px;background:#fff;cursor:pointer}
    .estado-select:focus{outline:none;border-color:#D3B8AD}

    /* Filtros */
    .filtros{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
    .filtros span{font-size:.75rem;color:#999;text-transform:uppercase;letter-spacing:.5px;font-weight:600}
    .filtro{padding:5px 14px;border:1px solid #ddd;border-radius:16px;font-size:.75rem;text-decoration:none;color:#666;transition:all .3s}
    .filtro:hover{border-color:#D3B8AD;color:#2c2c2c}
    .filtro.active{background:#2c2c2c;color:#fff;border-color:#2c2c2c}

    /* Mensaje preview */
    .msg-preview{max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:help}

    /* Tab content */
    .tab-content{display:none}
    .tab-content.active{display:block}
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

  <!-- Tabs -->
  <div class="tabs">
    <a href="?tab=visitas" class="tab <?= $tab === 'visitas' ? 'active' : '' ?>">Visitas</a>
    <a href="?tab=contacto" class="tab <?= $tab === 'contacto' ? 'active' : '' ?>">
      Contacto
      <?php if ($contacto_nuevos > 0): ?><span class="badge"><?= $contacto_nuevos ?></span><?php endif; ?>
    </a>
    <a href="?tab=speaking" class="tab <?= $tab === 'speaking' ? 'active' : '' ?>">
      Speaking
      <?php if ($speaking_nuevos > 0): ?><span class="badge"><?= $speaking_nuevos ?></span><?php endif; ?>
    </a>
    <a href="?tab=descargas" class="tab <?= $tab === 'descargas' ? 'active' : '' ?>">Descargas</a>
  </div>

  <!-- ==================== VISITAS ==================== -->
  <div class="tab-content <?= $tab === 'visitas' ? 'active' : '' ?>">
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
        <div class="card__number"><?= number_format($total_visitas) ?></div>
        <div class="card__label">Total historico</div>
      </div>
    </div>

    <div class="section">
      <h2>Ultimos 30 dias</h2>
      <canvas id="chart"></canvas>
    </div>

    <div class="section">
      <h2>Ultimas visitas</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Pagina</th><th>IP</th><th>Referrer</th><th>Fecha</th></tr></thead>
          <tbody>
            <?php foreach ($ultimas as $v): ?>
            <tr>
              <td><?= htmlspecialchars($v['pagina']) ?></td>
              <td><?= htmlspecialchars($v['ip']) ?></td>
              <td><?= htmlspecialchars($v['referrer'] ?: '-') ?></td>
              <td style="white-space:nowrap"><?= $v['fecha'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ==================== CONTACTO ==================== -->
  <div class="tab-content <?= $tab === 'contacto' ? 'active' : '' ?>">
    <div class="cards">
      <div class="card">
        <div class="card__number"><?= $contacto_nuevos ?></div>
        <div class="card__label">Nuevos</div>
      </div>
      <div class="card">
        <div class="card__number"><?= $contacto_total ?></div>
        <div class="card__label">Total</div>
      </div>
    </div>

    <div class="section">
      <h2>Mensajes de contacto</h2>
      <div class="filtros">
        <span>Filtrar:</span>
        <a href="?tab=contacto" class="filtro <?= !$filtro_contacto ? 'active' : '' ?>">Todos</a>
        <a href="?tab=contacto&estado_contacto=nuevo" class="filtro <?= $filtro_contacto === 'nuevo' ? 'active' : '' ?>">Nuevo</a>
        <a href="?tab=contacto&estado_contacto=contactado" class="filtro <?= $filtro_contacto === 'contactado' ? 'active' : '' ?>">Contactado</a>
        <a href="?tab=contacto&estado_contacto=aceptado" class="filtro <?= $filtro_contacto === 'aceptado' ? 'active' : '' ?>">Aceptado</a>
        <a href="?tab=contacto&estado_contacto=descartado" class="filtro <?= $filtro_contacto === 'descartado' ? 'active' : '' ?>">Descartado</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Fecha</th><th>Nombre</th><th>Email</th><th>Servicio</th><th>Mensaje</th><th>Estado</th><th>Cambiar</th></tr></thead>
          <tbody>
            <?php if (empty($contactos)): ?>
            <tr><td colspan="7" style="text-align:center;color:#999;padding:32px">No hay mensajes</td></tr>
            <?php endif; ?>
            <?php foreach ($contactos as $c): ?>
            <tr>
              <td style="white-space:nowrap"><?= $c['fecha'] ?></td>
              <td><?= htmlspecialchars($c['nombre']) ?></td>
              <td><a href="mailto:<?= htmlspecialchars($c['email']) ?>"><?= htmlspecialchars($c['email']) ?></a></td>
              <td><?= htmlspecialchars($c['servicio'] ?: '-') ?></td>
              <td><span class="msg-preview" title="<?= htmlspecialchars($c['mensaje'] ?? '') ?>"><?= htmlspecialchars($c['mensaje'] ?? '-') ?></span></td>
              <td><span class="estado estado--<?= $c['estado'] ?>"><?= ucfirst($c['estado']) ?></span></td>
              <td>
                <form method="post" style="margin:0">
                  <input type="hidden" name="cambiar_estado" value="1">
                  <input type="hidden" name="tabla" value="contacto">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <input type="hidden" name="tab" value="contacto">
                  <select name="estado" class="estado-select" onchange="this.form.submit()">
                    <option value="nuevo" <?= $c['estado'] === 'nuevo' ? 'selected' : '' ?>>Nuevo</option>
                    <option value="contactado" <?= $c['estado'] === 'contactado' ? 'selected' : '' ?>>Contactado</option>
                    <option value="aceptado" <?= $c['estado'] === 'aceptado' ? 'selected' : '' ?>>Aceptado</option>
                    <option value="descartado" <?= $c['estado'] === 'descartado' ? 'selected' : '' ?>>Descartado</option>
                  </select>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ==================== SPEAKING ==================== -->
  <div class="tab-content <?= $tab === 'speaking' ? 'active' : '' ?>">
    <div class="cards">
      <div class="card">
        <div class="card__number"><?= $speaking_nuevos ?></div>
        <div class="card__label">Nuevos</div>
      </div>
      <div class="card">
        <div class="card__number"><?= $speaking_total ?></div>
        <div class="card__label">Total</div>
      </div>
    </div>

    <div class="section">
      <h2>Solicitudes de speaking</h2>
      <div class="filtros">
        <span>Filtrar:</span>
        <a href="?tab=speaking" class="filtro <?= !$filtro_speaking ? 'active' : '' ?>">Todos</a>
        <a href="?tab=speaking&estado_speaking=nuevo" class="filtro <?= $filtro_speaking === 'nuevo' ? 'active' : '' ?>">Nuevo</a>
        <a href="?tab=speaking&estado_speaking=contactado" class="filtro <?= $filtro_speaking === 'contactado' ? 'active' : '' ?>">Contactado</a>
        <a href="?tab=speaking&estado_speaking=aceptado" class="filtro <?= $filtro_speaking === 'aceptado' ? 'active' : '' ?>">Aceptado</a>
        <a href="?tab=speaking&estado_speaking=descartado" class="filtro <?= $filtro_speaking === 'descartado' ? 'active' : '' ?>">Descartado</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Fecha</th><th>Nombre</th><th>Email</th><th>Telefono</th><th>Pais</th><th>Fecha evento</th><th>Asistentes</th><th>Estado</th><th>Cambiar</th></tr></thead>
          <tbody>
            <?php if (empty($speakings)): ?>
            <tr><td colspan="9" style="text-align:center;color:#999;padding:32px">No hay solicitudes</td></tr>
            <?php endif; ?>
            <?php foreach ($speakings as $s): ?>
            <tr>
              <td style="white-space:nowrap"><?= $s['fecha'] ?></td>
              <td><?= htmlspecialchars($s['nombre']) ?></td>
              <td><a href="mailto:<?= htmlspecialchars($s['email']) ?>"><?= htmlspecialchars($s['email']) ?></a></td>
              <td><?= htmlspecialchars($s['telefono'] ?: '-') ?></td>
              <td><?= htmlspecialchars($s['pais'] ?: '-') ?></td>
              <td style="white-space:nowrap"><?= $s['fecha_evento'] ?: '-' ?></td>
              <td><?= $s['asistentes'] ?: '-' ?></td>
              <td><span class="estado estado--<?= $s['estado'] ?>"><?= ucfirst($s['estado']) ?></span></td>
              <td>
                <form method="post" style="margin:0">
                  <input type="hidden" name="cambiar_estado" value="1">
                  <input type="hidden" name="tabla" value="speaking">
                  <input type="hidden" name="id" value="<?= $s['id'] ?>">
                  <input type="hidden" name="tab" value="speaking">
                  <select name="estado" class="estado-select" onchange="this.form.submit()">
                    <option value="nuevo" <?= $s['estado'] === 'nuevo' ? 'selected' : '' ?>>Nuevo</option>
                    <option value="contactado" <?= $s['estado'] === 'contactado' ? 'selected' : '' ?>>Contactado</option>
                    <option value="aceptado" <?= $s['estado'] === 'aceptado' ? 'selected' : '' ?>>Aceptado</option>
                    <option value="descartado" <?= $s['estado'] === 'descartado' ? 'selected' : '' ?>>Descartado</option>
                  </select>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ==================== DESCARGAS ==================== -->
  <div class="tab-content <?= $tab === 'descargas' ? 'active' : '' ?>">
    <div class="cards">
      <div class="card">
        <div class="card__number"><?= $descargas_total ?></div>
        <div class="card__label">Total descargas</div>
      </div>
    </div>

    <div class="section">
      <h2>Ultimas descargas</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Fecha</th><th>Libro</th><th>Nombre</th><th>Email</th><th>IP</th></tr></thead>
          <tbody>
            <?php if (empty($descargas)): ?>
            <tr><td colspan="5" style="text-align:center;color:#999;padding:32px">No hay descargas</td></tr>
            <?php endif; ?>
            <?php foreach ($descargas as $d): ?>
            <tr>
              <td style="white-space:nowrap"><?= $d['fecha'] ?></td>
              <td><?= htmlspecialchars($d['libro']) ?></td>
              <td><?= htmlspecialchars($d['nombre'] ?: '-') ?></td>
              <td><a href="mailto:<?= htmlspecialchars($d['email']) ?>"><?= htmlspecialchars($d['email']) ?></a></td>
              <td><?= htmlspecialchars($d['ip']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
  <script>
    if (document.getElementById('chart')) {
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
    }
  </script>
</body>
</html>
