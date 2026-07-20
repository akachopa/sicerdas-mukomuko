<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\DataTable;
use App\Core\Notification;
use App\Core\Upload;

class ReportController extends Controller
{
    public function index(): void
    {
        $this->render('reports/index', [
            'title' => __t('report.title'),
            'canAdd' => in_array(Auth::role(), ['super_admin', 'admin_bapperida', 'mitra'], true),
        ]);
    }

    public function data(): void
    {
        $where = '';
        $params = [];
        if (Auth::role() === 'mitra') {
            $where = ' WHERE r.organization_id = ?';
            $params[] = (int) (Auth::user()['organization_id'] ?? 0);
        }

        DataTable::respond(
            "SELECT r.id, r.number, r.registration_number, r.status, r.submitted_at,
                    o.name AS org_name, fy.year, rp.name AS period,
                    (SELECT COALESCE(SUM(i.realized_amount),0) FROM csr_report_items i WHERE i.csr_report_id = r.id) AS total_realized",
            "FROM csr_reports r
             JOIN organizations o ON o.id = r.organization_id
             JOIN fiscal_years fy ON fy.id = r.fiscal_year_id
             JOIN reporting_periods rp ON rp.id = r.reporting_period_id" . $where,
            [null, 'r.number', 'o.name', 'fy.year', 'rp.name', null, 'r.status', null],
            $params,
            function (array $row, int $no): array {
                $actions = '<div class="text-end text-nowrap">'
                    . '<a class="btn btn-sm btn-outline-secondary me-1" href="/laporan/' . $row['id'] . '" title="' . __t('common.detail') . '"><i class="bi bi-eye"></i></a>'
                    . '<a class="btn btn-sm btn-outline-secondary" href="/laporan/' . $row['id'] . '/cetak" target="_blank" title="' . __t('common.print') . '"><i class="bi bi-printer"></i></a>'
                    . '</div>';
                return [
                    $no,
                    '<span class="text-muted small">' . e($row['number']) . '</span>'
                        . ($row['registration_number'] ? '<br><small class="text-success">' . e($row['registration_number']) . '</small>' : ''),
                    '<strong>' . e($row['org_name']) . '</strong>',
                    e($row['year']),
                    e($row['period']),
                    '<div class="text-end">' . format_rupiah($row['total_realized']) . '</div>',
                    status_badge($row['status']),
                    $actions,
                ];
            },
            'ORDER BY r.id DESC'
        );
    }

    public function create(): void
    {
        $this->render('reports/form', [
            'title' => __t('report.add'),
            'report' => null,
            'items' => [],
            'refs' => $this->refs(),
        ]);
    }

    public function store(): void
    {
        $header = $this->collectHeader();
        if ($header === null) {
            flash('danger', __t('common.required_fields'));
            redirect('/laporan/tambah');
        }
        $items = $this->collectItems();
        if ($items === []) {
            flash('danger', __t('report.need_item'));
            redirect('/laporan/tambah');
        }

        $year = date('Y');
        $count = (int) Database::scalar("SELECT COUNT(*) FROM csr_reports WHERE number LIKE ?", ["LAP-$year-%"]);
        $header['number'] = sprintf('LAP-%s-%03d', $year, $count + 1);
        $header['status'] = 'draft';
        $header['created_by'] = Auth::id();

        $id = Database::insert('csr_reports', $header);
        foreach ($items as $item) {
            $item['csr_report_id'] = $id;
            Database::insert('csr_report_items', $item);
        }
        Audit::log('create', 'csr_reports', $id);
        flash('success', __t('common.saved'));
        redirect('/laporan/' . $id);
    }

    public function show(string $id): void
    {
        $report = $this->find((int) $id);
        $this->render('reports/show', [
            'title' => $report['number'],
            'report' => $report,
            'items' => $this->items((int) $report['id']),
            'documents' => Database::select("SELECT * FROM csr_report_documents WHERE csr_report_id = ? ORDER BY id DESC", [$report['id']]),
            'notes' => Database::select(
                "SELECT vn.*, u.full_name FROM verification_notes vn
                 JOIN users u ON u.id = vn.user_id
                 WHERE vn.csr_report_id = ? ORDER BY vn.id DESC", [$report['id']]),
        ]);
    }

    public function edit(string $id): void
    {
        $report = $this->find((int) $id);
        if (!in_array($report['status'], ['draft', 'perlu_perbaikan'], true)) {
            flash('danger', 'Laporan tidak dapat diubah pada status ini.');
            redirect('/laporan/' . $id);
        }
        $this->render('reports/form', [
            'title' => __t('report.edit'),
            'report' => $report,
            'items' => $this->items((int) $report['id']),
            'refs' => $this->refs(),
        ]);
    }

    public function update(string $id): void
    {
        $report = $this->find((int) $id);
        if (!in_array($report['status'], ['draft', 'perlu_perbaikan'], true)) {
            flash('danger', 'Laporan tidak dapat diubah pada status ini.');
            redirect('/laporan/' . $id);
        }
        $header = $this->collectHeader();
        $items = $this->collectItems();
        if ($header === null || $items === []) {
            flash('danger', $header === null ? __t('common.required_fields') : __t('report.need_item'));
            redirect('/laporan/' . $id . '/ubah');
        }
        Database::update('csr_reports', $header, 'id = ?', [(int) $id]);
        Database::execute("DELETE FROM csr_report_items WHERE csr_report_id = ?", [(int) $id]);
        foreach ($items as $item) {
            $item['csr_report_id'] = (int) $id;
            Database::insert('csr_report_items', $item);
        }
        Audit::log('update', 'csr_reports', (int) $id);
        flash('success', __t('common.updated'));
        redirect('/laporan/' . $id);
    }

    public function submit(string $id): void
    {
        $report = $this->find((int) $id);
        if (!in_array($report['status'], ['draft', 'perlu_perbaikan'], true)) {
            redirect('/laporan/' . $id);
        }
        $itemCount = (int) Database::scalar("SELECT COUNT(*) FROM csr_report_items WHERE csr_report_id = ?", [(int) $id]);
        if ($itemCount === 0) {
            flash('danger', __t('report.need_item'));
            redirect('/laporan/' . $id);
        }
        $newStatus = $report['status'] === 'perlu_perbaikan' ? 'revisi_dikirim' : 'dikirim';
        Database::update('csr_reports', [
            'status' => $newStatus,
            'submitted_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $id]);
        Database::update('organizations', ['compliance_status' => 'sudah_melapor'], 'id = ?', [$report['organization_id']]);
        Audit::log('submit', 'csr_reports', (int) $id);
        Notification::sendToRole('admin_bapperida', 'Laporan CSR masuk',
            'Laporan ' . $report['number'] . ' dari ' . $report['org_name'] . ' menunggu verifikasi.', '/laporan/' . $id);
        Notification::sendToRole('verifikator', 'Laporan CSR masuk',
            'Laporan ' . $report['number'] . ' dari ' . $report['org_name'] . ' menunggu verifikasi.', '/laporan/' . $id);
        flash('success', __t('report.submitted'));
        redirect('/laporan/' . $id);
    }

    public function verify(string $id): void
    {
        $report = $this->find((int) $id);
        $decision = (string) $this->input('decision', '');
        $note = (string) $this->input('note', '');

        if (!in_array($report['status'], ['dikirim', 'revisi_dikirim', 'sedang_diperiksa'], true)) {
            redirect('/laporan/' . $id);
        }

        if ($decision === 'approve') {
            $year = date('Y');
            $count = (int) Database::scalar("SELECT COUNT(*) FROM csr_reports WHERE registration_number IS NOT NULL AND registration_number LIKE ?", ["REG-$year-%"]);
            Database::update('csr_reports', [
                'status' => 'terverifikasi',
                'registration_number' => $report['registration_number'] ?: sprintf('REG-%s-%04d', $year, $count + 1),
                'verified_at' => date('Y-m-d H:i:s'),
                'verified_by' => Auth::id(),
                'locked_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [(int) $id]);
            $this->addNote((int) $id, 'persetujuan', $note !== '' ? $note : 'Laporan disetujui.');
            Audit::log('approve', 'csr_reports', (int) $id);
            $this->notifyOrg($report, 'Laporan terverifikasi', 'Laporan ' . $report['number'] . ' telah diverifikasi dan dikunci.');
            flash('success', __t('report.verified'));
        } elseif ($decision === 'revision') {
            Database::update('csr_reports', ['status' => 'perlu_perbaikan'], 'id = ?', [(int) $id]);
            $this->addNote((int) $id, 'revisi', $note);
            Audit::log('request_revision', 'csr_reports', (int) $id);
            $this->notifyOrg($report, 'Laporan perlu perbaikan', 'Laporan ' . $report['number'] . ' perlu perbaikan: ' . $note);
            flash('warning', __t('report.revision_requested'));
        } elseif ($decision === 'reject') {
            Database::update('csr_reports', ['status' => 'ditolak'], 'id = ?', [(int) $id]);
            $this->addNote((int) $id, 'penolakan', $note);
            Audit::log('reject', 'csr_reports', (int) $id);
            $this->notifyOrg($report, 'Laporan ditolak', 'Laporan ' . $report['number'] . ' ditolak: ' . $note);
            flash('danger', __t('common.updated'));
        }
        redirect('/laporan/' . $id);
    }

    public function uploadDocument(string $id): void
    {
        $report = $this->find((int) $id);
        try {
            $file = Upload::handle($_FILES['document'] ?? [], 'laporan');
            if ($file !== null) {
                Database::insert('csr_report_documents', [
                    'csr_report_id' => $report['id'],
                    'doc_type' => (string) $this->input('doc_type', 'Dokumentasi'),
                    'file_name' => $file['name'],
                    'file_path' => $file['path'],
                    'file_size' => $file['size'],
                    'mime_type' => $file['mime'],
                    'uploaded_by' => Auth::id(),
                ]);
                Audit::log('upload', 'csr_report_documents', (int) $report['id']);
                flash('success', __t('common.saved'));
            }
        } catch (\RuntimeException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('/laporan/' . $id);
    }

    public function printView(string $id): void
    {
        $report = $this->find((int) $id);
        $this->render('reports/print', [
            'title' => $report['number'],
            'report' => $report,
            'items' => $this->items((int) $report['id']),
        ], 'layouts/print');
    }

    // ===== Internal =====

    private function addNote(int $reportId, string $type, string $note): void
    {
        if ($note === '') {
            return;
        }
        Database::insert('verification_notes', [
            'csr_report_id' => $reportId,
            'user_id' => Auth::id(),
            'note_type' => $type,
            'note' => $note,
        ]);
    }

    private function notifyOrg(array $report, string $title, string $message): void
    {
        $users = Database::select(
            "SELECT id FROM users WHERE organization_id = ? AND is_active = 1", [$report['organization_id']]);
        foreach ($users as $u) {
            Notification::send((int) $u['id'], $title, $message, '/laporan/' . $report['id']);
        }
    }

    private function find(int $id): array
    {
        $report = Database::selectOne(
            "SELECT r.*, o.name AS org_name, fy.year, rp.name AS period,
                    vu.full_name AS verifier_name
             FROM csr_reports r
             JOIN organizations o ON o.id = r.organization_id
             JOIN fiscal_years fy ON fy.id = r.fiscal_year_id
             JOIN reporting_periods rp ON rp.id = r.reporting_period_id
             LEFT JOIN users vu ON vu.id = r.verified_by
             WHERE r.id = ?", [$id]);
        if ($report === null) {
            http_response_code(404);
            exit(__t('common.not_found'));
        }
        if (Auth::role() === 'mitra' && (int) $report['organization_id'] !== (int) (Auth::user()['organization_id'] ?? 0)) {
            http_response_code(403);
            exit(__t('error.403'));
        }
        return $report;
    }

    private function items(int $reportId): array
    {
        return Database::select(
            "SELECT i.*, pf.name AS field_name, d.name AS district_name, v.name AS village_name,
                    fs.name AS funding_source, ct.name AS contribution_type
             FROM csr_report_items i
             LEFT JOIN program_fields pf ON pf.id = i.program_field_id
             LEFT JOIN districts d ON d.id = i.district_id
             LEFT JOIN villages v ON v.id = i.village_id
             LEFT JOIN funding_sources fs ON fs.id = i.funding_source_id
             LEFT JOIN contribution_types ct ON ct.id = i.contribution_type_id
             WHERE i.csr_report_id = ? ORDER BY i.id", [$reportId]);
    }

    private function refs(): array
    {
        return [
            'organizations' => Database::select("SELECT id, name FROM organizations WHERE is_active = 1 ORDER BY name"),
            'fiscal_years' => Database::select("SELECT id, year FROM fiscal_years WHERE is_active = 1 ORDER BY year DESC"),
            'periods' => Database::select("SELECT id, name FROM reporting_periods WHERE is_active = 1 ORDER BY id"),
            'fields' => Database::select("SELECT id, name FROM program_fields WHERE is_active = 1 ORDER BY name"),
            'districts' => Database::select("SELECT id, name FROM districts WHERE is_active = 1 ORDER BY name"),
            'funding_sources' => Database::select("SELECT id, name FROM funding_sources WHERE is_active = 1 ORDER BY name"),
            'contribution_types' => Database::select("SELECT id, name FROM contribution_types WHERE is_active = 1 ORDER BY name"),
        ];
    }

    private function collectHeader(): ?array
    {
        $yearId = (int) $this->input('fiscal_year_id', 0);
        $periodId = (int) $this->input('reporting_period_id', 0);
        $orgId = Auth::role() === 'mitra'
            ? (int) (Auth::user()['organization_id'] ?? 0)
            : (int) $this->input('organization_id', 0);
        $responsible = (string) $this->input('responsible_name', '');
        if ($yearId === 0 || $periodId === 0 || $orgId === 0 || $responsible === '') {
            return null;
        }
        return [
            'organization_id' => $orgId,
            'fiscal_year_id' => $yearId,
            'reporting_period_id' => $periodId,
            'responsible_name' => $responsible,
            'responsible_position' => $this->input('responsible_position') ?: null,
            'notes' => $this->input('notes') ?: null,
        ];
    }

    private function collectItems(): array
    {
        $items = [];
        $names = $_POST['item_name'] ?? [];
        $money = fn(string $v): float => (float) str_replace(['.', ','], ['', '.'], $v);
        foreach ((array) $names as $idx => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $realized = $money((string) ($_POST['item_realized'][$idx] ?? '0'));
            if ($realized < 0) {
                continue;
            }
            $items[] = [
                'activity_name' => $name,
                'program_field_id' => ($_POST['item_field'][$idx] ?? '') !== '' ? (int) $_POST['item_field'][$idx] : null,
                'district_id' => ($_POST['item_district'][$idx] ?? '') !== '' ? (int) $_POST['item_district'][$idx] : null,
                'location_detail' => trim((string) ($_POST['item_location'][$idx] ?? '')) ?: null,
                'planned_amount' => $money((string) ($_POST['item_planned'][$idx] ?? '0')),
                'realized_amount' => $realized,
                'funding_source_id' => ($_POST['item_source'][$idx] ?? '') !== '' ? (int) $_POST['item_source'][$idx] : null,
                'contribution_type_id' => ($_POST['item_contribution'][$idx] ?? '') !== '' ? (int) $_POST['item_contribution'][$idx] : null,
                'benefit' => trim((string) ($_POST['item_benefit'][$idx] ?? '')) ?: null,
                'beneficiary_count' => (int) ($_POST['item_beneficiaries'][$idx] ?? 0),
                'beneficiary_type' => trim((string) ($_POST['item_beneficiary_type'][$idx] ?? '')) ?: null,
                'obstacles' => trim((string) ($_POST['item_obstacles'][$idx] ?? '')) ?: null,
            ];
        }
        return $items;
    }
}
