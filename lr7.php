<?php
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function ulen($s) { return preg_match_all('/./us', $s); }

$sent = $_SERVER['REQUEST_METHOD'] === 'POST';
$v = [];
foreach (['name', 'email', 'age', 'height', 'weight'] as $k) $v[$k] = trim($_POST[$k] ?? '');

$err = [];
if ($sent) {
  if (ulen($v['name']) < 2 || ulen($v['name']) > 50) $err[] = 'Ім’я має бути більше ніж 2 символи ';
  if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $err[] = 'Невірний e-mail.';
  if (!ctype_digit($v['age']) || (int)$v['age'] < 1 || (int)$v['age'] > 120) $err[] = 'Вік — число від 1 до 120.';
  $h = str_replace(',', '.', $v['height']);
  $w = str_replace(',', '.', $v['weight']);
  if (!is_numeric($h) || $h < 50 || $h > 250) $err[] = 'Зріст — від 50 до 250 см.';
  if (!is_numeric($w) || $w < 10 || $w > 400) $err[] = 'Вага — від 10 до 400 кг.';

  if (!$err) {
    $m = $h / 100;
    $bmi = $w / ($m * $m);
    if ($bmi < 18.5) $cat = 'Недостатня вага';
    elseif ($bmi < 25) $cat = 'Норма';
    elseif ($bmi < 30) $cat = 'Надлишкова вага';
    else $cat = 'Ожиріння';
    $minW = 18.5 * $m * $m;
    $maxW = 24.9 * $m * $m;
  }
}
?>
<!DOCTYPE html>
<html lang="uk">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ЛР 7 — Результат</title>
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
        <a href="lr7.html" class="active">ЛР 7</a>
        <a href="lr8.php">ЛР 8</a>
      </nav>
    </div>
  </header>
  <main class="container">
    <h1>ЛР 7</h1>
    <div class="card accent">
      <h2>Результат</h2>
      <?php if (!$sent): ?>
        <div class="msg err">Дані не передано. Заповніть форму.</div>
      <?php elseif ($err): ?>
        <div class="msg err"><?= implode('<br>', array_map('e', $err)) ?></div>
      <?php else: ?>
        <p><?= e($v['name']) ?> (<?= e($v['age']) ?> р.), результат для <?= e($v['email']) ?>.</p>
        <div class="stats" style="margin-top:10px">
          <div class="stat"><b><?= number_format($bmi, 1) ?></b>ІМТ</div>
          <div class="stat"><b style="font-size:16px"><?= $cat ?></b>категорія</div>
        </div>
        <p class="muted">Зріст: <?= e($v['height']) ?> см, вага: <?= e($v['weight']) ?> кг.</p>
        <p class="muted">Нормальна вага для вашого зросту: <?= number_format($minW, 1) ?>–<?= number_format($maxW, 1) ?> кг.</p>
      <?php endif; ?>
      <a class="btn ghost small" href="lr7.html" style="margin-top:12px">← Назад до форми</a>
    </div>
  </main>
  <footer class="site">
    <div class="container">Богдан, 2381</div>
  </footer>
</body>

</html>
