<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('program.title') ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard"><?= __t('common.dashboard') ?></a></li>
                <li class="breadcrumb-item active"><?= __t('menu.programs') ?></li>
            </ol>
        </nav>
    </div>
    <?php if ($canManage): ?>
        <a href="/program/tambah" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i><?= __t('program.add') ?></a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="tableProgram">
                <thead>
                <tr>
                    <th><?= __t('common.no') ?></th>
                    <th><?= __t('program.code') ?></th>
                    <th><?= __t('program.name') ?></th>
                    <th><?= __t('program.field') ?></th>
                    <th><?= __t('common.district') ?></th>
                    <th class="text-end"><?= __t('program.budget_needed') ?></th>
                    <th><?= __t('program.priority') ?></th>
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
    initDataTable('#tableProgram', '/program/data');
});
</script>
