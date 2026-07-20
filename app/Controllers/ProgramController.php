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
use App\Models\ProgramModel;

class ProgramController extends Controller
{
    public function index(): void
    {
        $this->render('programs/index', [
            'title' => __t('program.title'),
            'canManage' => in_array(Auth::role(), ['super_admin', 'admin_bapperida', 'opd'], true),
        ]);
    }

    public function data(): void
    {
        $where = '';
        $params = [];
        // OPD hanya melihat program miliknya
        if (Auth::role() === 'opd') {
            $where = ' WHERE p.department_id = ?';
            $params[] = (int) (Auth::user()['department_id'] ?? 0);
        }

        $committed = ProgramModel::COMMITTED_SQL;
        DataTable::respond(
            "SELECT p.id, p.code, p.name, p.status, p.priority_level, p.budget_needed,
                    d.short_name AS dept, pf.name AS field, dist.name AS district,
                    $committed AS committed",
            "FROM programs p
             JOIN departments d ON d.id = p.department_id
             JOIN program_fields pf ON pf.id = p.program_field_id
             LEFT JOIN districts dist ON dist.id = p.district_id" . $where,
            [null, 'p.code', 'p.name', 'pf.name', 'dist.name', 'p.budget_needed', null, 'p.status', null],
            $params,
            function (array $row, int $no): array {
                $gap = max(0, (float) $row['budget_needed'] - (float) $row['committed']);
                $pct = (float) $row['budget_needed'] > 0
                    ? min(100, round((float) $row['committed'] / (float) $row['budget_needed'] * 100))
                    : 0;
                $canEdit = in_array($row['status'], ['draft', 'perlu_revisi'], true);
                $actions = '<div class="text-end text-nowrap">'
                    . '<a class="btn btn-sm btn-outline-secondary me-1" href="/program/' . $row['id'] . '" title="' . __t('common.detail') . '"><i class="bi bi-eye"></i></a>';
                if ($canEdit && in_array(Auth::role(), ['super_admin', 'admin_bapperida', 'opd'], true)) {
                    $actions .= '<a class="btn btn-sm btn-outline-primary" href="/program/' . $row['id'] . '/ubah" title="' . __t('common.edit') . '"><i class="bi bi-pencil"></i></a>';
                }
                $actions .= '</div>';
                return [
                    $no,
                    '<span class="text-muted small">' . e($row['code']) . '</span>',
                    '<strong>' . e($row['name']) . '</strong><br><small class="text-muted">' . e($row['dept']) . '</small>',
                    e($row['field']),
                    e($row['district'] ?? '-'),
                    '<div class="text-end">' . format_rupiah($row['budget_needed'])
                        . '<div class="progress mt-1" style="height:5px"><div class="progress-bar bg-success" style="width:' . $pct . '%"></div></div>'
                        . '<small class="text-muted">' . __t('program.funding_gap') . ': ' . format_rupiah($gap) . '</small></div>',
                    '<span class="badge text-bg-' . match ($row['priority_level']) {
                        'mendesak' => 'danger', 'tinggi' => 'warning', 'sedang' => 'info', default => 'secondary'
                    } . '">' . __t('priority.' . $row['priority_level']) . '</span>',
                    status_badge($row['status']),
                    $actions,
                ];
            },
            'ORDER BY p.id DESC'
        );
    }

    public function create(): void
    {
        $this->render('programs/form', [
            'title' => __t('program.add'),
            'program' => null,
            'refs' => $this->refs(),
        ]);
    }

    public function store(): void
    {
        $data = $this->collect();
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            redirect('/program/tambah');
        }
        $data['code'] = ProgramModel::generateCode();
        $data['created_by'] = Auth::id();
        $data['status'] = 'draft';
        $id = Database::insert('programs', $data);
        Audit::log('create', 'programs', $id, null, $data);
        flash('success', __t('common.saved'));
        redirect('/program/' . $id);
    }

    public function show(string $id): void
    {
        $program = $this->find((int) $id);
        // Mitra hanya boleh melihat program terpublikasi
        if (Auth::role() === 'mitra' && !$program['is_published']) {
            http_response_code(403);
            exit(__t('error.403'));
        }
        $this->render('programs/show', [
            'title' => $program['name'],
            'program' => $program,
            'funding' => ProgramModel::funding((int) $program['id']),
            'documents' => Database::select("SELECT * FROM program_documents WHERE program_id = ? ORDER BY id DESC", [$program['id']]),
            'commitments' => Database::select(
                "SELECT c.*, o.name AS org_name FROM commitments c
                 JOIN organizations o ON o.id = c.organization_id
                 WHERE c.program_id = ? ORDER BY c.id DESC", [$program['id']]),
            'interests' => Database::select(
                "SELECT pi.*, o.name AS org_name FROM program_interests pi
                 JOIN organizations o ON o.id = pi.organization_id
                 WHERE pi.program_id = ? ORDER BY pi.id DESC", [$program['id']]),
        ]);
    }

    public function edit(string $id): void
    {
        $program = $this->findOwned((int) $id);
        if (!in_array($program['status'], ['draft', 'perlu_revisi'], true)) {
            flash('danger', 'Program tidak dapat diubah pada status ini.');
            redirect('/program/' . $id);
        }
        $this->render('programs/form', [
            'title' => __t('program.edit'),
            'program' => $program,
            'refs' => $this->refs(),
        ]);
    }

    public function update(string $id): void
    {
        $before = $this->findOwned((int) $id);
        if (!in_array($before['status'], ['draft', 'perlu_revisi'], true)) {
            flash('danger', 'Program tidak dapat diubah pada status ini.');
            redirect('/program/' . $id);
        }
        $data = $this->collect();
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            redirect('/program/' . $id . '/ubah');
        }
        Database::update('programs', $data, 'id = ?', [(int) $id]);
        Audit::log('update', 'programs', (int) $id, $before, $data);
        flash('success', __t('common.updated'));
        redirect('/program/' . $id);
    }

    public function submit(string $id): void
    {
        $program = $this->findOwned((int) $id);
        if (!in_array($program['status'], ['draft', 'perlu_revisi'], true)) {
            redirect('/program/' . $id);
        }
        // Validasi minimal sesuai rencana
        $missing = [];
        foreach (['name', 'department_id', 'district_id', 'beneficiary_target', 'output', 'indicator'] as $field) {
            if (empty($program[$field])) {
                $missing[] = $field;
            }
        }
        if ((float) $program['budget_needed'] <= 0) {
            $missing[] = 'budget_needed';
        }
        if ($missing !== []) {
            flash('danger', __t('common.required_fields') . ' (' . implode(', ', $missing) . ')');
            redirect('/program/' . $id . '/ubah');
        }
        Database::update('programs', ['status' => 'menunggu_verifikasi'], 'id = ?', [(int) $id]);
        Audit::log('submit', 'programs', (int) $id);
        Notification::sendToRole('admin_bapperida', 'Usulan program baru',
            'Program "' . $program['name'] . '" menunggu verifikasi.', '/program/' . $id);
        Notification::sendToRole('verifikator', 'Usulan program baru',
            'Program "' . $program['name'] . '" menunggu verifikasi.', '/program/' . $id);
        flash('success', __t('program.submitted'));
        redirect('/program/' . $id);
    }

    public function verify(string $id): void
    {
        $program = $this->find((int) $id);
        $decision = (string) $this->input('decision', '');
        $note = (string) $this->input('note', '');

        if ($decision === 'approve') {
            Database::update('programs', ['status' => 'terverifikasi', 'revision_note' => null], 'id = ?', [(int) $id]);
            Audit::log('verify', 'programs', (int) $id, null, ['decision' => 'approve']);
            flash('success', __t('program.verified'));
        } elseif ($decision === 'revision') {
            Database::update('programs', ['status' => 'perlu_revisi', 'revision_note' => $note], 'id = ?', [(int) $id]);
            Audit::log('verify', 'programs', (int) $id, null, ['decision' => 'revision', 'note' => $note]);
            $this->notifyOwner($program, 'Program perlu revisi', 'Program "' . $program['name'] . '" dikembalikan untuk revisi. ' . $note);
            flash('warning', __t('report.revision_requested'));
        }
        redirect('/program/' . $id);
    }

    public function publish(string $id): void
    {
        $program = $this->find((int) $id);
        if ($program['status'] !== 'terverifikasi') {
            flash('danger', 'Hanya program terverifikasi yang dapat dipublikasikan.');
            redirect('/program/' . $id);
        }
        Database::update('programs', ['status' => 'dipublikasikan', 'is_published' => 1], 'id = ?', [(int) $id]);
        Audit::log('publish', 'programs', (int) $id);
        flash('success', __t('program.published'));
        redirect('/program/' . $id);
    }

    public function uploadDocument(string $id): void
    {
        $program = $this->findOwned((int) $id);
        try {
            $file = Upload::handle($_FILES['document'] ?? [], 'program');
            if ($file !== null) {
                Database::insert('program_documents', [
                    'program_id' => $program['id'],
                    'doc_type' => (string) $this->input('doc_type', 'Dokumen'),
                    'file_name' => $file['name'],
                    'file_path' => $file['path'],
                    'file_size' => $file['size'],
                    'mime_type' => $file['mime'],
                    'uploaded_by' => Auth::id(),
                ]);
                Audit::log('upload', 'program_documents', (int) $program['id']);
                flash('success', __t('common.saved'));
            }
        } catch (\RuntimeException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('/program/' . $id);
    }

    // ===== Internal =====

    private function notifyOwner(array $program, string $title, string $message): void
    {
        if (!empty($program['created_by'])) {
            Notification::send((int) $program['created_by'], $title, $message, '/program/' . $program['id']);
        }
    }

    private function find(int $id): array
    {
        $program = Database::selectOne(
            "SELECT p.*, d.name AS dept_name, d.short_name AS dept_short, pf.name AS field_name,
                    fy.year, dist.name AS district_name, v.name AS village_name
             FROM programs p
             JOIN departments d ON d.id = p.department_id
             JOIN program_fields pf ON pf.id = p.program_field_id
             JOIN fiscal_years fy ON fy.id = p.fiscal_year_id
             LEFT JOIN districts dist ON dist.id = p.district_id
             LEFT JOIN villages v ON v.id = p.village_id
             WHERE p.id = ?", [$id]);
        if ($program === null) {
            http_response_code(404);
            exit(__t('common.not_found'));
        }
        return $program;
    }

    private function findOwned(int $id): array
    {
        $program = $this->find($id);
        if (Auth::role() === 'opd' && (int) $program['department_id'] !== (int) (Auth::user()['department_id'] ?? 0)) {
            http_response_code(403);
            exit(__t('error.403'));
        }
        return $program;
    }

    private function refs(): array
    {
        return [
            'fiscal_years' => Database::select("SELECT id, year FROM fiscal_years WHERE is_active = 1 ORDER BY year DESC"),
            'departments' => Database::select("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name"),
            'fields' => Database::select("SELECT id, name FROM program_fields WHERE is_active = 1 ORDER BY name"),
            'districts' => Database::select("SELECT id, name FROM districts WHERE is_active = 1 ORDER BY name"),
            'villages' => Database::select("SELECT id, name, district_id FROM villages WHERE is_active = 1 ORDER BY name"),
        ];
    }

    private function collect(): ?array
    {
        $name = (string) $this->input('name', '');
        $deptId = (int) $this->input('department_id', 0);
        $fieldId = (int) $this->input('program_field_id', 0);
        $yearId = (int) $this->input('fiscal_year_id', 0);
        if ($name === '' || $fieldId === 0 || $yearId === 0) {
            return null;
        }
        // OPD terkunci pada departemennya sendiri
        if (Auth::role() === 'opd') {
            $deptId = (int) (Auth::user()['department_id'] ?? 0);
        }
        if ($deptId === 0) {
            return null;
        }
        $money = fn(string $key): float => (float) str_replace(['.', ','], ['', '.'], (string) $this->input($key, '0'));
        return [
            'name' => $name,
            'fiscal_year_id' => $yearId,
            'department_id' => $deptId,
            'program_field_id' => $fieldId,
            'description' => $this->input('description') ?: null,
            'background' => $this->input('background') ?: null,
            'objective' => $this->input('objective') ?: null,
            'district_id' => $this->input('district_id') ?: null,
            'village_id' => $this->input('village_id') ?: null,
            'location_detail' => $this->input('location_detail') ?: null,
            'beneficiary_target' => $this->input('beneficiary_target') ?: null,
            'beneficiary_count' => (int) $this->input('beneficiary_count', 0),
            'budget_needed' => $money('budget_needed'),
            'output' => $this->input('output') ?: null,
            'outcome' => $this->input('outcome') ?: null,
            'indicator' => $this->input('indicator') ?: null,
            'priority_level' => in_array($this->input('priority_level'), ['rendah', 'sedang', 'tinggi', 'mendesak'], true)
                ? $this->input('priority_level') : 'sedang',
            'start_date' => $this->input('start_date') ?: null,
            'end_date' => $this->input('end_date') ?: null,
        ];
    }
}
