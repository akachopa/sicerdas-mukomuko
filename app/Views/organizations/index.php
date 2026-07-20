<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('org.title') ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard"><?= __t('common.dashboard') ?></a></li>
                <li class="breadcrumb-item active"><?= __t('menu.organizations') ?></li>
            </ol>
        </nav>
    </div>
    <a href="/organisasi/tambah" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i><?= __t('org.add') ?></a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="tableOrg">
                <thead>
                <tr>
                    <th><?= __t('common.no') ?></th>
                    <th><?= __t('org.name') ?></th>
                    <th><?= __t('org.sector') ?></th>
                    <th><?= __t('common.district') ?></th>
                    <th><?= __t('org.pic_name') ?></th>
                    <th><?= __t('org.compliance') ?></th>
                    <th class="text-end"><?= __t('common.actions') ?></th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
$(function () {
    const table = initDataTable('#tableOrg', '/organisasi/data');
    $('#tableOrg').on('click', '.btn-toggle', function () {
        postAction('/organisasi/' + $(this).data('id') + '/toggle').done(() => table.ajax.reload(null, false));
    });
});
</script>
