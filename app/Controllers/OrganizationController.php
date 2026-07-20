<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\DataTable;
use App\Core\Upload;

class OrganizationController extends Controller
{
    public function index(): void
    {
        $this->render('organizations/index', ['title' => __t('org.title')]);
    }

    public function data(): void
    {
        DataTable::respond(
            "SELECT o.id, o.name, o.compliance_status, o.is_active, o.phone, o.pic_name,
                    et.name AS entity_type, bs.name AS sector, d.name AS district",
            "FROM organizations o
             LEFT JOIN entity_types et ON et.id = o.entity_type_id
             LEFT JOIN business_sectors bs ON bs.id = o.business_sector_id
             LEFT JOIN districts d ON d.id = o.district_id",
            [null, 'o.name', 'bs.name', 'd.name', 'o.pic_name', 'o.compliance_status', null],
            [],
            function (array $row, int $no): array {
                $toggleLabel = $row['is_active'] ? __t('common.deactivate') : __t('common.activate');
                $actions = '<div class="text-end text-nowrap">'
                    . '<a class="btn btn-sm btn-outline-secondary me-1" href="/organisasi/' . $row['id'] . '" title="' . __t('common.detail') . '"><i class="bi bi-eye"></i></a>'
                    . '<a class="btn btn-sm btn-outline-primary me-1" href="/organisasi/' . $row['id'] . '/ubah" title="' . __t('common.edit') . '"><i class="bi bi-pencil"></i></a>'
                    . '<button class="btn btn-sm btn-outline-secondary btn-toggle" data-id="' . $row['id'] . '" title="' . $toggleLabel . '"><i class="bi ' . ($row['is_active'] ? 'bi-toggle-on' : 'bi-toggle-off') . '"></i></button>'
                    . '</div>';
                return [
                    $no,
                    '<strong>' . e($row['name']) . '</strong>' . ($row['entity_type'] ? '<br><small class="text-muted">' . e($row['entity_type']) . '</small>' : ''),
                    e($row['sector'] ?? '-'),
                    e($row['district'] ?? '-'),
                    e($row['pic_name'] ?? '-'),
                    status_badge($row['compliance_status']),
                    $actions,
                ];
            },
            'ORDER BY o.id DESC'
        );
    }

    public function create(): void
    {
        $this->render('organizations/form', [
            'title' => __t('org.add'),
            'org' => null,
            'refs' => $this->refs(),
        ]);
    }

    public function store(): void
    {
        $data = $this->collect();
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            $this->keepOld();
            redirect('/organisasi/tambah');
        }
        $id = Database::insert('organizations', $data);
        Audit::log('create', 'organizations', $id, null, $data);
        $this->clearOld();
        flash('success', __t('common.saved'));
        redirect('/organisasi/' . $id);
    }

    public function show(string $id): void
    {
        $org = $this->find((int) $id);
        $this->render('organizations/show', [
            'title' => $org['name'],
            'org' => $org,
            'documents' => Database::select("SELECT * FROM organization_documents WHERE organization_id = ? ORDER BY id DESC", [$org['id']]),
            'commitments' => Database::select(
                "SELECT c.*, p.name AS program_name FROM commitments c JOIN programs p ON p.id = c.program_id
                 WHERE c.organization_id = ? ORDER BY c.id DESC LIMIT 20", [$org['id']]),
            'reports' => Database::select(
                "SELECT r.*, fy.year, rp.name AS period FROM csr_reports r
                 JOIN fiscal_years fy ON fy.id = r.fiscal_year_id
                 JOIN reporting_periods rp ON rp.id = r.reporting_period_id
                 WHERE r.organization_id = ? ORDER BY r.id DESC LIMIT 20", [$org['id']]),
            'totals' => Database::selectOne(
                "SELECT COALESCE(SUM(c.amount),0) AS committed,
                        COALESCE((SELECT SUM(rz.amount) FROM realizations rz
                                  JOIN commitments c2 ON c2.id = rz.commitment_id
                                  WHERE c2.organization_id = ? AND rz.status = 'terverifikasi'),0) AS realized
                 FROM commitments c
                 WHERE c.organization_id = ? AND c.status NOT IN ('draft','dibatalkan','kedaluwarsa')",
                [$org['id'], $org['id']]),
        ]);
    }

    public function edit(string $id): void
    {
        $org = $this->find((int) $id);
        $this->render('organizations/form', [
            'title' => __t('org.edit'),
            'org' => $org,
            'refs' => $this->refs(),
        ]);
    }

    public function update(string $id): void
    {
        $before = $this->find((int) $id);
        $data = $this->collect();
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            redirect('/organisasi/' . $id . '/ubah');
        }
        Database::update('organizations', $data, 'id = ?', [(int) $id]);
        Audit::log('update', 'organizations', (int) $id, $before, $data);
        flash('success', __t('common.updated'));
        redirect('/organisasi/' . $id);
    }

    public function toggle(string $id): void
    {
        Database::execute("UPDATE organizations SET is_active = 1 - is_active WHERE id = ?", [(int) $id]);
        Audit::log('toggle', 'organizations', (int) $id);
        json_response(['ok' => true]);
    }

    public function uploadDocument(string $id): void
    {
        $org = $this->find((int) $id);
        $this->storeDocument($org, '/organisasi/' . $id);
    }

    // ===== Portal mitra: profil sendiri =====

    public function myProfile(): void
    {
        $org = $this->myOrg();
        $this->render('organizations/my_profile', [
            'title' => __t('menu.company_profile'),
            'org' => $org,
            'refs' => $this->refs(),
            'documents' => Database::select("SELECT * FROM organization_documents WHERE organization_id = ? ORDER BY id DESC", [$org['id']]),
        ]);
    }

    public function updateMyProfile(): void
    {
        $org = $this->myOrg();
        $data = $this->collect(ownProfile: true);
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            redirect('/profil-perusahaan');
        }
        Database::update('organizations', $data, 'id = ?', [$org['id']]);
        Audit::log('update', 'organizations', (int) $org['id'], $org, $data);
        flash('success', __t('common.updated'));
        redirect('/profil-perusahaan');
    }

    public function uploadMyDocument(): void
    {
        $this->storeDocument($this->myOrg(), '/profil-perusahaan');
    }

    // ===== Internal =====

    private function storeDocument(array $org, string $redirectTo): void
    {
        try {
            $file = Upload::handle($_FILES['document'] ?? [], 'organisasi');
            if ($file === null) {
                flash('danger', __t('upload.failed'));
                redirect($redirectTo);
            }
            $docId = Database::insert('organization_documents', [
                'organization_id' => $org['id'],
                'doc_type' => (string) $this->input('doc_type', 'Dokumen'),
                'file_name' => $file['name'],
                'file_path' => $file['path'],
                'file_size' => $file['size'],
                'mime_type' => $file['mime'],
                'uploaded_by' => Auth::id(),
            ]);
            Audit::log('upload', 'organization_documents', $docId);
            flash('success', __t('common.saved'));
        } catch (\RuntimeException $e) {
            flash('danger', $e->getMessage());
        }
        redirect($redirectTo);
    }

    private function myOrg(): array
    {
        $orgId = (int) (Auth::user()['organization_id'] ?? 0);
        $org = Database::selectOne("SELECT * FROM organizations WHERE id = ?", [$orgId]);
        if ($org === null) {
            http_response_code(404);
            exit(__t('common.not_found'));
        }
        return $org;
    }

    private function find(int $id): array
    {
        $org = Database::selectOne(
            "SELECT o.*, et.name AS entity_type, bs.name AS sector, d.name AS district_name, v.name AS village_name
             FROM organizations o
             LEFT JOIN entity_types et ON et.id = o.entity_type_id
             LEFT JOIN business_sectors bs ON bs.id = o.business_sector_id
             LEFT JOIN districts d ON d.id = o.district_id
             LEFT JOIN villages v ON v.id = o.village_id
             WHERE o.id = ?", [$id]);
        if ($org === null) {
            http_response_code(404);
            exit(__t('common.not_found'));
        }
        return $org;
    }

    private function refs(): array
    {
        return [
            'entity_types' => Database::select("SELECT id, name FROM entity_types WHERE is_active = 1 ORDER BY name"),
            'sectors' => Database::select("SELECT id, name FROM business_sectors WHERE is_active = 1 ORDER BY name"),
            'districts' => Database::select("SELECT id, name FROM districts WHERE is_active = 1 ORDER BY name"),
            'villages' => Database::select("SELECT id, name, district_id FROM villages WHERE is_active = 1 ORDER BY name"),
        ];
    }

    private function collect(bool $ownProfile = false): ?array
    {
        $name = (string) $this->input('name', '');
        if ($name === '') {
            return null;
        }
        $data = [
            'name' => $name,
            'legal_name' => $this->input('legal_name') ?: null,
            'entity_type_id' => $this->input('entity_type_id') ?: null,
            'business_sector_id' => $this->input('business_sector_id') ?: null,
            'nib' => $this->input('nib') ?: null,
            'npwp' => $this->input('npwp') ?: null,
            'address' => $this->input('address') ?: null,
            'district_id' => $this->input('district_id') ?: null,
            'village_id' => $this->input('village_id') ?: null,
            'website' => $this->input('website') ?: null,
            'email' => $this->input('email') ?: null,
            'phone' => $this->input('phone') ?: null,
            'pic_name' => $this->input('pic_name') ?: null,
            'pic_position' => $this->input('pic_position') ?: null,
            'pic_phone' => $this->input('pic_phone') ?: null,
            'pic_email' => $this->input('pic_email') ?: null,
            'employee_count' => $this->input('employee_count') !== '' ? (int) $this->input('employee_count') : null,
            'local_employee_count' => $this->input('local_employee_count') !== '' ? (int) $this->input('local_employee_count') : null,
            'established_year' => $this->input('established_year') !== '' ? (int) $this->input('established_year') : null,
        ];
        if (!$ownProfile) {
            $data['csr_potential'] = $this->input('csr_potential') !== '' ? (float) str_replace(['.', ','], ['', '.'], (string) $this->input('csr_potential')) : null;
            $data['compliance_status'] = (string) $this->input('compliance_status', 'terdaftar');
            $data['notes'] = $this->input('notes') ?: null;
        }
        return $data;
    }
}
