<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('report.title') ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard"><?= __t('common.dashboard') ?></a></li>
                <li class="breadcrumb-item active"><?= __t('menu.reports') ?></li>
            </ol>
        </nav>
    </div>
    <?php if ($canAdd): ?>
        <a href="/laporan/tambah" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i><?= __t('report.add') ?></a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="tableReport">
                <thead>
                <tr>
                    <th><?= __t('common.no') ?></th>
                    <th><?= __t('report.number') ?></th>
                    <th><?= __t('report.organization') ?></th>
                    <th><?= __t('common.year') ?></th>
                    <th><?= __t('common.period') ?></th>
                    <th class="text-end"><?= __t('report.realized') ?></th>
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
    initDataTable('#tableReport', '/laporan/data');
});
</script>
