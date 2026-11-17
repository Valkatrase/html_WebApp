<?php
//
const DB = __DIR__ . '/lr8_users.json';
const CK = 'auth_token';
const DAYS = 30;

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function users() { return is_file(DB) ? (json_decode(file_get_contents(DB), true) ?: []) : []; }
function save($u) { file_put_contents(DB, json_encode($u, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX); }

function setCk($value, $expires) {
  setcookie(CK, $value, ['expires' => $expires, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
}
function delCk() {
  setcookie(CK, '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
  unset($_COOKIE[CK]);
}

function issueToken(&$u, $lg, $remember) {
  $tk = bin2hex(random_bytes(32));
  $exp = $remember ? time() + DAYS * 86400 : 0;
  $u[$lg]['token'] = hash('sha256', $tk);
  $u[$lg]['token_exp'] = $exp;
  save($u);
  setCk($lg . ':' . $tk, $exp);
}

function userFromCookie($u) {
  $c = $_COOKIE[CK] ?? '';
  $i = strpos($c, ':');
  if ($i === false) return null;
  $lg = substr($c, 0, $i);
  $tk = substr($c, $i + 1);
  if (isset($u[$lg]) && !empty($u[$lg]['token']) && hash_equals($u[$lg]['token'], hash('sha256', $tk))) return $lg;
  return null;
}

$u = users();
$flash = '';
$ok = false;
$action = $_POST['action'] ?? '';
$old = ['l1' => '', 'l2' => '', 'e2' => ''];

if ($action === 'login') {
  $lg = strtolower(trim($_POST['login'] ?? ''));
  $old['l1'] = $lg;
  if (isset($u[$lg]) && password_verify($_POST['password'] ?? '', $u[$lg]['hash'])) {
    issueToken($u, $lg, !empty($_POST['remember']));
    header('Location: lr8.php?m=in');
    exit;
  }
  $flash = 'Невірний логін або пароль. Якщо ви ще не зареєстровані — заповніть форму реєстрації.';
} elseif ($action === 'register') {
  $lg = strtolower(trim($_POST['login'] ?? ''));
  $em = trim($_POST['email'] ?? '');
  $p1 = $_POST['password'] ?? '';
  $p2 = $_POST['password2'] ?? '';
  $old['l2'] = $lg;
  $old['e2'] = $em;
  $err = [];
  if (!preg_match('/^[a-z0-9_]{3,20}$/', $lg)) $err[] = 'Логін цифри або символи';
  if (!filter_var($em, FILTER_VALIDATE_EMAIL)) $err[] = 'Не вірний e-mail.';
  if (strlen($p1) < 6) $err[] = 'Пароль  не менше ніж 6 символів.';
  if ($p1 !== $p2) $err[] = 'Пароль не зберігся.';
  if (isset($u[$lg])) $err[] = 'Цей логін вже використовуеться.';
  if ($err) {
    $flash = implode('<br>', array_map('e', $err));
  } else {
    $u[$lg] = ['email' => $em, 'hash' => password_hash($p1, PASSWORD_DEFAULT), 'created' => date('d.m.Y, H:i:s'), 'token' => '', 'token_exp' => 0];
    issueToken($u, $lg, true);
    header('Location: lr8.php?m=reg');
    exit;
  }
} elseif ($action === 'logout') {
  $lg = userFromCookie($u);
  if ($lg) { $u[$lg]['token'] = ''; $u[$lg]['token_exp'] = 0; save($u); }
  delCk();
  header('Location: lr8.php?m=out');
  exit;
}

$user = userFromCookie($u);
if (!$user && isset($_COOKIE[CK])) delCk();

$m = $_GET['m'] ?? '';
$via = ($m === 'in' || $m === 'reg') ? 'за паролем' : 'через Cookie';
if (!$flash) {
  if ($user && $m === 'in') { $flash = 'Вхід успішний.'; $ok = true; }
  elseif ($user && $m === 'reg') { $flash = 'Реєстрацію завершено. Ви успішно увійшли до акаунту.'; $ok = true; }
  elseif ($m === 'out') { $flash = 'Ви вийшли з акаунта.'; $ok = true; }
  elseif ($user) { $flash = 'Автоматичний вхід.'; $ok = true; }
}
?>
<!DOCTYPE html>
<html lang="uk">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ЛР 8</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <header class="site">
    <div class="container">
      <div class="brand">WebApp</div>
      <nav class="menu">
        <a href="index.html">ЛР 1</a>
        <a href="lr2.html">ЛР 2</a>
        <a href="lr3.html">ЛР 3</a>
        <a href="lr4.html">ЛР 4</a>
        <a href="lr5.php">ЛР 5</a>
        <a href="lr6.php">ЛР 6</a>
        <a href="lr7.html">ЛР 7</a>
        <a href="lr8.php" class="active">ЛР 8</a>
      </nav>
    </div>
  </header>
  <main class="container">
    <h1>ЛР 8</h1>
    <div class="msg <?= $flash ? ($ok ? 'ok' : 'err') : '' ?>"><?= $flash ?></div>
    <div style="margin-top:16px">
      <?php if ($user): $info = $u[$user]; ?>
        <div class="card accent">
          <h2>Вітаємо, <?= e($user) ?>!</h2>
          <div class="stats">
            <div class="stat"><b style="font-size:16px"><?= e($info['email']) ?></b>e-mail</div>
            <div class="stat"><b style="font-size:16px"><?= e($info['created']) ?></b>дата реєстрації</div>
            <div class="stat"><b style="font-size:16px"><?= $via ?></b>спосіб входу</div>
          </div>
          <p class="muted" style="margin-bottom:12px">Cookie автовходу:
            <?= $info['token_exp'] ? 'є (діє до ' . date('d.m.Y', $info['token_exp']) . ')' : 'до закриття браузера' ?>.</p>
          <form method="post"><input type="hidden" name="action" value="logout"><button class="btn" type="submit">Вийти</button></form>
        </div>
      <?php else: ?>
        <div class="grid-2">
          <div class="card accent">
            <h2>Вхід</h2>
            <form method="post">
              <input type="hidden" name="action" value="login">
              <div class="field"><label for="l1">Логін</label><input id="l1" name="login" value="<?= e($old['l1']) ?>" required></div>
              <div class="field"><label for="p1">Пароль</label><input id="p1" name="password" type="password" required></div>
              <div class="field"><label><input type="checkbox" id="rem" name="remember" value="1">Запам’ятати мене на 30 днів</label></div>
              <button class="btn" type="submit">Увійти</button>
            </form>
          </div>
          <div class="card">
            <h2>Реєстрація</h2>
            <form method="post">
              <input type="hidden" name="action" value="register">
              <div class="field"><label for="l2">Логін</label><input id="l2" name="login" value="<?= e($old['l2']) ?>" required></div>
              <div class="field"><label for="e2">E-mail</label><input id="e2" name="email" type="email" value="<?= e($old['e2']) ?>" required></div>
              <div class="field"><label for="p2">Пароль</label><input id="p2" name="password" type="password" required></div>
              <div class="field"><label for="p3">Повторіть пароль</label><input id="p3" name="password2" type="password" required></div>
              <button class="btn" type="submit">Зареєструватися</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>
  <footer class="site">
    <div class="container">Богдан, 2381</div>
  </footer>
</body>

</html>
