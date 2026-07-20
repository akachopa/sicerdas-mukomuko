<?php use App\Core\Auth; ?>
<div class="kop d-flex align-items-center gap-3">
    <span class="d-inline-flex align-items-center justify-content-center"
          style="width:56px;height:56px;border-radius:12px;background:linear-gradient(135deg,#c9a227,#e6c65a);color:#0f2a52;font-weight:800;font-size:1.3rem;">SC</span>
    <div>
        <h1 class="h5 fw-bold mb-0" style="color:#0f2a52">SICERDAS MUKOMUKO</h1>
        <div class="small text-muted"><?= __t('app.tagline') ?></div>
        <div class="small text-muted">Pemerintah Kabupaten Mukomuko</div>
    </div>
</div>

<h2 class="h5 text-center fw-bold mb-4"><?= e($data['title']) ?></h2>

<table class="table table-sm table-bordered">
    <thead class="table-light">
    <tr><?php foreach ($data['headers'] as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr>
    </thead>
    <tbody>
    <?php if ($data['rows'] === []): ?>
        <tr><td colspan="<?= count($data['headers']) ?>" class="text-center text-muted"><?= __t('common.none') ?></td></tr>
    <?php else: foreach ($data['rows'] as $row): ?>
        <tr><?php foreach ($row as $cell): ?><td><?= e((string) $cell) ?></td><?php endforeach; ?></tr>
    <?php endforeach; endif; ?>
    </tbody>
</table>

<p class="small text-muted mt-4">
    <?= __t('recap.printed_at') ?>: <?= date('d/m/Y H:i') ?> —
    <?= __t('recap.printed_by') ?>: <?= e(Auth::user()['full_name']) ?>
</p>
