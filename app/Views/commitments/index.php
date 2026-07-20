<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('commitment.title') ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard"><?= __t('common.dashboard') ?></a></li>
                <li class="breadcrumb-item active"><?= __t('menu.commitments') ?></li>
            </ol>
        </nav>
    </div>
    <?php if ($isAdmin): ?>
        <a href="/komitmen/tambah" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i><?= __t('commitment.add') ?></a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="tableCommitment">
                <thead>
                <tr>
                    <th><?= __t('common.no') ?></th>
                    <th><?= __t('commitment.number') ?></th>
                    <th><?= __t('commitment.organization') ?></th>
                    <th><?= __t('commitment.program') ?></th>
                    <th class="text-end"><?= __t('commitment.amount') ?></th>
                    <th class="text-end"><?= __t('program.realized') ?></th>
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
    initDataTable('#tableCommitment', '/komitmen/data');
});
</script>
