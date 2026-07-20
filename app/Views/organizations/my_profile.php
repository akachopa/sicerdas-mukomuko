<?php use App\Core\Csrf; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h2 class="page-title"><?= __t('menu.company_profile') ?></h2>
        <p class="text-muted small mb-0"><?= e($org['name']) ?> — <?= status_badge($org['compliance_status']) ?></p>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <form method="post" action="/profil-perusahaan/update">
            <?= Csrf::field() ?>
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-buildings me-1"></i> <?= __t('org.title') ?></div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label required"><?= __t('org.name') ?></label>
                        <input class="form-control" name="name" required value="<?= e($org['name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('org.legal_name') ?></label>
                        <input class="form-control" name="legal_name" value="<?= e($org['legal_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('org.entity_type') ?></label>
                        <select class="form-select" name="entity_type_id">
                            <option value=""><?= __t('common.choose') ?></option>
                            <?php foreach ($refs['entity_types'] as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($org['entity_type_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('org.sector') ?></label>
                        <select class="form-select" name="business_sector_id">
                            <option value=""><?= __t('common.choose') ?></option>
                            <?php foreach ($refs['sectors'] as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($org['business_sector_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('org.nib') ?></label>
                        <input class="form-control" name="nib" value="<?= e($org['nib'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('org.npwp') ?></label>
                        <input class="form-control" name="npwp" value="<?= e($org['npwp'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= __t('common.address') ?></label>
                        <textarea class="form-control" name="address" rows="2"><?= e($org['address'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('common.district') ?></label>
                        <select class="form-select" name="district_id">
                            <option value=""><?= __t('common.choose') ?></option>
                            <?php foreach ($refs['districts'] as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($org['district_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?= __t('common.email') ?></label>
                        <input type="email" class="form-control" name="email" value="<?= e($org['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?= __t('common.phone') ?></label>
                        <input class="form-control" name="phone" value="<?= e($org['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('org.pic_name') ?></label>
                        <input class="form-control" name="pic_name" value="<?= e($org['pic_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('org.pic_position') ?></label>
                        <input class="form-control" name="pic_position" value="<?= e($org['pic_position'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('org.employee_count') ?></label>
                        <input type="number" class="form-control" name="employee_count" min="0" value="<?= e($org['employee_count'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('org.local_employee') ?></label>
                        <input type="number" class="form-control" name="local_employee_count" min="0" value="<?= e($org['local_employee_count'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('org.established_year') ?></label>
                        <input type="number" class="form-control" name="established_year" min="1900" max="2100" value="<?= e($org['established_year'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <button class="btn btn-primary px-4" type="submit"><i class="bi bi-save me-1"></i><?= __t('common.save') ?></button>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-folder2-open me-1"></i> <?= __t('common.documents') ?></div>
            <div class="card-body">
                <form method="post" action="/profil-perusahaan/dokumen" enctype="multipart/form-data" class="mb-3">
                    <?= Csrf::field() ?>
                    <div class="mb-2">
                        <input class="form-control form-control-sm" name="doc_type" placeholder="<?= __t('upload.doc_type') ?>" required>
                    </div>
                    <div class="input-group input-group-sm">
                        <input class="form-control" type="file" name="document" required>
                        <button class="btn btn-primary"><?= __t('common.upload') ?></button>
                    </div>
                </form>
                <?php if ($documents === []): ?>
                    <p class="text-muted small mb-0"><?= __t('common.none') ?></p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($documents as $doc): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="small"><i class="bi bi-file-earmark me-1 text-muted"></i><?= e($doc['doc_type']) ?></span>
                                <a href="<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
