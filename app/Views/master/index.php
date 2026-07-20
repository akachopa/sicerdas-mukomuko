<?php use App\Core\Csrf; ?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= e($cfg['label']) ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard"><?= __t('common.dashboard') ?></a></li>
                <li class="breadcrumb-item"><?= __t('menu.master_data') ?></li>
                <li class="breadcrumb-item active"><?= e($cfg['label']) ?></li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm" id="btnAdd">
        <i class="bi bi-plus-lg me-1"></i><?= __t('common.add') ?>
    </button>
</div>

<div class="row g-3">
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header"><i class="bi bi-database-gear me-1"></i><?= __t('menu.master_data') ?></div>
            <div class="list-group list-group-flush">
                <?php foreach ($masterMenus as $mKey => $mLabel): ?>
                    <a href="/master/<?= e($mKey) ?>"
                       class="list-group-item list-group-item-action<?= $mKey === $key ? ' active' : '' ?>">
                        <?= e($mLabel) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="tableMaster">
                        <thead>
                        <tr>
                            <th><?= __t('common.no') ?></th>
                            <?php foreach ($cfg['fields'] as $f): ?>
                                <th><?= e($f['label']) ?></th>
                            <?php endforeach; ?>
                            <th><?= __t('common.status') ?></th>
                            <th class="text-end"><?= __t('common.actions') ?></th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post" id="formMaster" action="/master/<?= e($key) ?>/store">
            <?= Csrf::field() ?>
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"><?= __t('common.add') ?> <?= e($cfg['label']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php foreach ($cfg['fields'] as $f): ?>
                    <div class="mb-3">
                        <label class="form-label<?= empty($f['optional']) ? ' required' : '' ?>"><?= e($f['label']) ?></label>
                        <?php if (in_array($f['type'], ['select', 'fk'], true)): ?>
                            <select class="form-select" name="<?= e($f['name']) ?>" <?= empty($f['optional']) ? 'required' : '' ?>>
                                <option value=""><?= __t('common.choose') ?></option>
                                <?php foreach ($f['options'] ?? [] as $val => $label): ?>
                                    <option value="<?= e($val) ?>"><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="<?= e($f['type']) ?>" class="form-control" name="<?= e($f['name']) ?>"
                                   <?= empty($f['optional']) ? 'required' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= __t('common.cancel') ?></button>
                <button type="submit" class="btn btn-primary"><?= __t('common.save') ?></button>
            </div>
        </form>
    </div>
</div>

<script>
$(function () {
    const table = initDataTable('#tableMaster', '/master/<?= e($key) ?>/data');
    const modal = new bootstrap.Modal('#modalForm');
    const form = document.getElementById('formMaster');

    document.getElementById('btnAdd').addEventListener('click', function () {
        form.action = '/master/<?= e($key) ?>/store';
        form.reset();
        document.getElementById('modalTitle').textContent = <?= json_encode(__t('common.add') . ' ' . $cfg['label']) ?>;
    });

    $('#tableMaster').on('click', '.btn-edit', function () {
        const row = $(this).data('row');
        form.action = '/master/<?= e($key) ?>/update/' + row.id;
        document.getElementById('modalTitle').textContent = <?= json_encode(__t('common.edit') . ' ' . $cfg['label']) ?>;
        Object.keys(row).forEach(function (k) {
            const el = form.querySelector('[name="' + k + '"]');
            if (el) el.value = row[k] ?? '';
        });
        modal.show();
    });

    $('#tableMaster').on('click', '.btn-toggle', function () {
        postAction('/master/<?= e($key) ?>/toggle/' + $(this).data('id')).done(() => table.ajax.reload(null, false));
    });
});
</script>
