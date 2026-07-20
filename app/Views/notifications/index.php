<?php use App\Core\Csrf; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="page-title"><?= __t('notif.title') ?></h2>
    <form method="post" action="/notifikasi/baca-semua"><?= Csrf::field() ?>
        <button class="btn btn-light"><i class="bi bi-check2-all me-1"></i><?= __t('notif.mark_all') ?></button>
    </form>
</div>

<div class="card">
    <div class="list-group list-group-flush">
        <?php if ($notifications === []): ?>
            <div class="list-group-item text-center text-muted py-5"><?= __t('notif.empty') ?></div>
        <?php else: foreach ($notifications as $n): ?>
            <a class="list-group-item list-group-item-action d-flex gap-3<?= $n['is_read'] ? '' : ' bg-light' ?>" href="/notifikasi/<?= $n['id'] ?>/buka">
                <i class="bi <?= $n['is_read'] ? 'bi-envelope-open text-muted' : 'bi-envelope-fill text-primary' ?> mt-1"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold"><?= e($n['title']) ?></div>
                    <div class="small text-muted"><?= e($n['message']) ?></div>
                </div>
                <small class="text-muted text-nowrap"><?= format_date($n['created_at']) ?></small>
            </a>
        <?php endforeach; endif; ?>
    </div>
</div>
