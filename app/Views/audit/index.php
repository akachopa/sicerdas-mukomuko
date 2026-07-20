<div class="mb-3">
    <h2 class="page-title"><?= __t('audit.title') ?></h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard"><?= __t('common.dashboard') ?></a></li>
            <li class="breadcrumb-item active"><?= __t('menu.audit') ?></li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="tableAudit">
                <thead>
                <tr>
                    <th><?= __t('common.no') ?></th>
                    <th><?= __t('audit.time') ?></th>
                    <th><?= __t('audit.user') ?></th>
                    <th><?= __t('audit.action') ?></th>
                    <th><?= __t('audit.module') ?></th>
                    <th><?= __t('audit.ip') ?></th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
$(function () {
    initDataTable('#tableAudit', '/audit/data', { noActionColumn: true, dt: { order: [] } });
});
</script>
