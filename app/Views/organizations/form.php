<?php use App\Core\Csrf; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h2 class="page-title"><?= e($title) ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/organisasi"><?= __t('menu.organizations') ?></a></li>
                <li class="breadcrumb-item active"><?= e($title) ?></li>
            </ol>
        </nav>
    </div>
    <a href="/organisasi" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i><?= __t('common.back') ?></a>
</div>

<form method="post" action="<?= $org ? '/organisasi/' . $org['id'] . '/update' : '/organisasi/simpan' ?>">
    <?= Csrf::field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-buildings me-1"></i> <?= __t('org.title') ?></div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label required"><?= __t('org.name') ?></label>
                        <input class="form-control" name="name" required value="<?= e($org['name'] ?? old('name')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('org.legal_name') ?></label>
                        <input class="form-control" name="legal_name" value="<?= e($org['legal_name'] ?? old('legal_name')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('org.entity_type') ?></label>
                        <select class="form-select" name="entity_type_id">
                            <option value=""><?= __t('common.choose') ?></option>
                            <?php foreach ($refs['entity_types'] as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($org['entity_type_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('org.sector') ?></label>
                        <select class="form-select" name="business_sector_id">
                            <option value=""><?= __t('common.choose') ?></option>
                            <?php foreach ($refs['sectors'] as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($org['business_sector_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('org.established_year') ?></label>
                        <input type="number" class="form-control" name="established_year" min="1900" max="2100" value="<?= e($org['established_year'] ?? '') ?>">
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
                        <select class="form-select" name="district_id" id="selDistrict">
                            <option value=""><?= __t('common.choose') ?></option>
                            <?php foreach ($refs['districts'] as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($org['district_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('common.village') ?></label>
                        <select class="form-select" name="village_id" id="selVillage" data-selected="<?= e($org['village_id'] ?? '') ?>">
                            <option value=""><?= __t('common.choose') ?></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('common.email') ?></label>
                        <input type="email" class="form-control" name="email" value="<?= e($org['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('common.phone') ?></label>
                        <input class="form-control" name="phone" value="<?= e($org['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= __t('org.website') ?></label>
                        <input class="form-control" name="website" value="<?= e($org['website'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="bi bi-person-badge me-1"></i> <?= __t('org.pic') ?></div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('org.pic_name') ?></label>
                        <input class="form-control" name="pic_name" value="<?= e($org['pic_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('org.pic_position') ?></label>
                        <input class="form-control" name="pic_position" value="<?= e($org['pic_position'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('common.phone') ?></label>
                        <input class="form-control" name="pic_phone" value="<?= e($org['pic_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __t('common.email') ?></label>
                        <input type="email" class="form-control" name="pic_email" value="<?= e($org['pic_email'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-info-circle me-1"></i> Administrasi</div>
                <div class="card-body row g-3">
                    <div class="col-6">
                        <label class="form-label"><?= __t('org.employee_count') ?></label>
                        <input type="number" class="form-control" name="employee_count" min="0" value="<?= e($org['employee_count'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label"><?= __t('org.local_employee') ?></label>
                        <input type="number" class="form-control" name="local_employee_count" min="0" value="<?= e($org['local_employee_count'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= __t('org.csr_potential') ?> (Rp)</label>
                        <input class="form-control" name="csr_potential" data-money value="<?= $org && $org['csr_potential'] !== null ? number_format((float) $org['csr_potential'], 0, ',', '.') : '' ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= __t('org.compliance') ?></label>
                        <select class="form-select" name="compliance_status">
                            <?php foreach (['terdaftar', 'profil_belum_lengkap', 'aktif', 'nonaktif', 'sudah_melapor', 'belum_melapor', 'perlu_tindak_lanjut', 'terverifikasi', 'ditangguhkan'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($org['compliance_status'] ?? 'terdaftar') === $s ? 'selected' : '' ?>><?= __t('status.' . $s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label"><?= __t('common.notes') ?></label>
                        <textarea class="form-control" name="notes" rows="3"><?= e($org['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary w-100 py-2" type="submit"><i class="bi bi-save me-1"></i><?= __t('common.save') ?></button>
        </div>
    </div>
</form>

<script>
const VILLAGES = <?= json_encode($refs['villages'], JSON_UNESCAPED_UNICODE) ?>;
$(function () {
    const $district = $('#selDistrict'), $village = $('#selVillage');
    function fillVillages() {
        const did = $district.val(), sel = $village.data('selected');
        $village.find('option:not(:first)').remove();
        VILLAGES.filter(v => String(v.district_id) === String(did)).forEach(v => {
            $village.append($('<option>').val(v.id).text(v.name).prop('selected', String(v.id) === String(sel)));
        });
    }
    $district.on('change', fillVillages);
    fillVillages();
});
</script>
