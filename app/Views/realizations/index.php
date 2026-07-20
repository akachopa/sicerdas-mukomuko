<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('realization.title') ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard"><?= __t('common.dashboard') ?></a></li>
                <li class="breadcrumb-item active"><?= __t('menu.realizations') ?></li>
            </ol>
        </nav>
    </div>
    <?php if ($canAdd): ?>
        <a href="/realisasi/tambah" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i><?= __t('realization.add') ?></a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="tableRealization">
                <thead>
                <tr>
                    <th><?= __t('common.no') ?></th>
                    <th><?= __t('realization.number') ?></th>
                    <th><?= __t('common.date') ?></th>
                    <th><?= __t('commitment.organization') ?></th>
                    <th><?= __t('commitment.program') ?></th>
                    <th class="text-end"><?= __t('common.amount') ?></th>
                    <th><?= __t('common.status') ?></th>
                    <th class="text-end"><?= __t('common.actions') ?></th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
$(function () {
    const table = initDataTable('#tableRealization', '/realisasi/data');
    $('#tableRealization').on('click', '.btn-verify', function () {
        postAction('/realisasi/' + $(this).data('id') + '/verifikasi').done(() => table.ajax.reload(null, false));
    });
});
</script>
