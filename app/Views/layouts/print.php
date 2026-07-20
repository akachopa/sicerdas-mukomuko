<?php use App\Core\Lang; ?>
<!DOCTYPE html>
<html lang="<?= Lang::current() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'SICERDAS') ?> — SICERDAS Mukomuko</title>
    <link rel="stylesheet" href="/assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        body { background: #fff; }
        .print-page { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem; }
        .kop { border-bottom: 3px double #0f2a52; padding-bottom: .75rem; margin-bottom: 1.25rem; }
    </style>
</head>
<body>
<div class="print-page">
    <?= $content ?>
</div>
<script>window.addEventListener('load', () => { if (new URLSearchParams(location.search).get('auto') !== '0') window.print(); });</script>
</body>
</html>
