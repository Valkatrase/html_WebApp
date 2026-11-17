<?php
$students = [
  'Олена'  => [5, 4, 5, 5, 4],
  'Максим' => [3, 4, 4, 3, 5],
  'Ірина'  => [5, 5, 5, 4, 5],
  'Дмитро' => [2, 3, 3, 4, 3],
  'Богдан' => [4, 5, 4, 5, 5],
];
$items = [
  ['Клавіатура', 1850, 2],
  ['Миша', 920, 3],
  ['Монітор 27"', 8400, 1],
  ['USB-хаб', 450, 4],
];
const VAT_RATE = 0.2;

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($x) { return number_format($x, 2, ',', ' ') . ' грн'; }

function factorial($n) { $r = 1; for ($i = 2; $i <= $n; $i++) $r *= $i; return $r; }

function fib($n) {
  $a = [0, 1];
  for ($i = 2; $i < $n; $i++) $a[] = $a[$i - 1] + $a[$i - 2];
  return array_slice($a, 0, $n);
}

function primes($max) {
  $s = array_fill(0, $max + 1, true);
  for ($i = 2; $i * $i <= $max; $i++)
    if ($s[$i]) for ($j = $i * $i; $j <= $max; $j += $i) $s[$j] = false;
  $res = [];
  for ($i = 2; $i <= $max; $i++) if ($s[$i]) $res[] = $i;
  return $res;
}

// Успішність
$st = [];
foreach ($students as $name => $g) {
  $st[] = ['name' => $name, 'g' => $g, 'avg' => array_sum($g) / count($g), 'max' => max($g), 'min' => min($g)];
}
usort($st, fn($a, $b) => $b['avg'] <=> $a['avg']);

//
$sub = 0;
foreach ($items as $i) $sub += $i[1] * $i[2];
$vat = $sub * VAT_RATE;

// Сума квадратів 1…10
$sumSq = 0;
for ($i = 1; $i <= 10; $i++) $sumSq += pow($i, 2);
?>
<!DOCTYPE html>
<html lang="uk">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ЛР 6</title>
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
        <a href="lr6.php" class="active">ЛР 6</a>
        <a href="lr7.html">ЛР 7</a>
        <a href="lr8.php">ЛР 8</a>
      </nav>
    </div>
  </header>
  <main class="container">
    <h1>ЛР 6</h1>

    <div class="card accent">
      <h2>Успішність студентів</h2>
      <div class="row head cols-5"><div>#</div><div>Студент</div><div>Середній</div><div>Макс.</div><div>Мін.</div></div>
      <?php foreach ($st as $i => $s): ?>
        <div class="row cols-5<?= $i === 0 ? ' best' : '' ?>">
          <div><?= $i + 1 ?></div>
          <div><?= e($s['name']) ?> <span class="muted">(<?= implode(', ', $s['g']) ?>)</span></div>
          <div><?= number_format($s['avg'], 2) ?></div>
          <div><?= $s['max'] ?></div>
          <div><?= $s['min'] ?></div>
        </div>
      <?php endforeach; ?>
      <p class="muted" style="margin-top:10px">Найвищий середній бал: <b><?= e($st[0]['name']) ?></b> (<?= number_format($st[0]['avg'], 2) ?>).</p>
    </div>

    <div class="card">
      <h2>Розрахунок замовлення</h2>
      <div class="row head cols-4"><div>Товар</div><div>Ціна × к-сть</div><div></div><div>Сума</div></div>
      <?php foreach ($items as $i): ?>
        <div class="row cols-4">
          <div><?= e($i[0]) ?></div>
          <div><?= money($i[1]) ?> × <?= $i[2] ?></div>
          <div></div>
          <div><?= money($i[1] * $i[2]) ?></div>
        </div>
      <?php endforeach; ?>
      <div class="stats" style="margin-top:12px">
        <div class="stat"><b><?= money($sub) ?></b>без ПДВ</div>
        <div class="stat"><b><?= money($vat) ?></b>ПДВ <?= VAT_RATE * 100 ?>%</div>
        <div class="stat"><b><?= money($sub + $vat) ?></b>до сплати</div>
      </div>
    </div>

    <div class="card">
      <h2>Математика</h2>
      <p>10! = <b><?= number_format(factorial(10), 0, '', ' ') ?></b></p>
      <p>Перші 15 чисел Фібоначчі: <b><?= implode(', ', fib(15)) ?></b></p>
      <p>Прості числа до 60: <b><?= implode(', ', primes(60)) ?></b></p>
      <p>Сума квадратів 1…10: <b><?= $sumSq ?></b></p>
    </div>
  </main>
  <footer class="site">
    <div class="container">Богдан, 2381</div>
  </footer>
</body>

</html>
