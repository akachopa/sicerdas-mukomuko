<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('user.title') ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard"><?= __t('common.dashboard') ?></a></li>
                <li class="breadcrumb-item active"><?= __t('menu.users') ?></li>
            </ol>
        </nav>
    </div>
    <a href="/pengguna/tambah" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i><?= __t('user.add') ?></a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="tableUsers">
                <thead>
                <tr>
                    <th><?= __t('common.no') ?></th>
                    <th><?= __t('user.full_name') ?></th>
                    <th><?= __t('common.email') ?></th>
                    <th><?= __t('user.role') ?></th>
                    <th><?= __t('user.last_login') ?></th>
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
    const table = initDataTable('#tableUsers', '/pengguna/data');
    $('#tableUsers').on('click', '.btn-toggle', function () {
        postAction('/pengguna/' + $(this).data('id') + '/toggle').done(() => table.ajax.reload(null, false));
    });
});
</script>
