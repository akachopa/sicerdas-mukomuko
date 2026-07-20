<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\DataTable;
use App\Core\Notification;
use App\Models\ProgramModel;

class CommitmentController extends Controller
{
    public function index(): void
    {
        $this->render('commitments/index', [
            'title' => __t('commitment.title'),
            'isAdmin' => Auth::isAdmin(),
        ]);
    }

    public function data(): void
    {
        $where = '';
        $params = [];
        if (Auth::role() === 'mitra') {
            $where = ' WHERE c.organization_id = ?';
            $params[] = (int) (Auth::user()['organization_id'] ?? 0);
        }

        DataTable::respond(
            "SELECT c.id, c.number, c.amount, c.status, c.mou_number,
                    o.name AS org_name, p.name AS program_name, ct.name AS contribution,
                    (SELECT COALESCE(SUM(rz.amount),0) FROM realizations rz
                     WHERE rz.commitment_id = c.id AND rz.status = 'terverifikasi') AS realized",
            "FROM commitments c
             JOIN organizations o ON o.id = c.organization_id
             JOIN programs p ON p.id = c.program_id
             LEFT JOIN contribution_types ct ON ct.id = c.contribution_type_id" . $where,
            [null, 'c.number', 'o.name', 'p.name', 'c.amount', null, 'c.status', null],
            $params,
            function (array $row, int $no): array {
                $pct = (float) $row['amount'] > 0 ? min(100, round((float) $row['realized'] / (float) $row['amount'] * 100)) : 0;
                $actions = '<div class="text-end text-nowrap">'
                    . '<a class="btn btn-sm btn-outline-secondary" href="/komitmen/' . $row['id'] . '" title="' . __t('common.detail') . '"><i class="bi bi-eye"></i></a>';
                if (Auth::isAdmin() && in_array($row['status'], ['draft', 'diajukan', 'dalam_pembahasan'], true)) {
                    $actions .= ' <a class="btn btn-sm btn-outline-primary" href="/komitmen/' . $row['id'] . '/ubah" title="' . __t('common.edit') . '"><i class="bi bi-pencil"></i></a>';
                }
                $actions .= '</div>';
                return [
                    $no,
                    '<span class="text-muted small">' . e($row['number']) . '</span>',
                    '<strong>' . e($row['org_name']) . '</strong>',
                    e($row['program_name']) . '<br><small class="text-muted">' . e($row['contribution'] ?? '-') . '</small>',
                    '<div class="text-end">' . format_rupiah($row['amount']) . '</div>',
                    '<div class="text-end">' . format_rupiah($row['realized'])
                        . '<div class="progress mt-1" style="height:5px"><div class="progress-bar bg-success" style="width:' . $pct . '%"></div></div></div>',
                    status_badge($row['status']),
                    $actions,
                ];
            },
            'ORDER BY c.id DESC'
        );
    }

    public function create(): void
    {
        $this->render('commitments/form', [
            'title' => __t('commitment.add'),
            'commitment' => null,
            'refs' => $this->refs(),
        ]);
    }

    public function store(): void
    {
        $data = $this->collect();
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            redirect('/komitmen/tambah');
        }
        $funding = ProgramModel::funding((int) $data['program_id']);
        if ($data['amount'] > $funding['gap']) {
            flash('danger', __t('commitment.exceeds_gap') . ' (' . format_rupiah($funding['gap']) . ')');
            redirect('/komitmen/tambah');
        }
        $data['number'] = $this->generateNumber();
        $data['created_by'] = Auth::id();
        $data['status'] = 'diajukan';
        $id = Database::insert('commitments', $data);
        Audit::log('create', 'commitments', $id, null, $data);
        flash('success', __t('common.saved'));
        redirect('/komitmen/' . $id);
    }

    public function show(string $id): void
    {
        $commitment = $this->find((int) $id);
        $this->render('commitments/show', [
            'title' => $commitment['number'],
            'commitment' => $commitment,
            'realizations' => Database::select(
                "SELECT * FROM realizations WHERE commitment_id = ? ORDER BY realization_date DESC", [$commitment['id']]),
        ]);
    }

    public function edit(string $id): void
    {
        $commitment = $this->find((int) $id);
        $this->render('commitments/form', [
            'title' => __t('commitment.edit'),
            'commitment' => $commitment,
            'refs' => $this->refs(),
        ]);
    }

    public function update(string $id): void
    {
        $before = $this->find((int) $id);
        $data = $this->collect();
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            redirect('/komitmen/' . $id . '/ubah');
        }
        Database::update('commitments', $data, 'id = ?', [(int) $id]);
        Audit::log('update', 'commitments', (int) $id, $before, $data);
        ProgramModel::refreshFundingStatus((int) $data['program_id']);
        flash('success', __t('common.updated'));
        redirect('/komitmen/' . $id);
    }

    public function changeStatus(string $id): void
    {
        $commitment = $this->find((int) $id);
        $status = (string) $this->input('status', '');
        $allowed = ['diajukan', 'dalam_pembahasan', 'disetujui', 'aktif', 'selesai', 'dibatalkan', 'kedaluwarsa'];
        if (!in_array($status, $allowed, true)) {
            flash('danger', __t('common.not_found'));
            redirect('/komitmen/' . $id);
        }
        Database::update('commitments', ['status' => $status], 'id = ?', [(int) $id]);
        Audit::log('status', 'commitments', (int) $id, ['status' => $commitment['status']], ['status' => $status]);
        ProgramModel::refreshFundingStatus((int) $commitment['program_id']);

        if ($status === 'disetujui') {
            $partnerUsers = Database::select(
                "SELECT id FROM users WHERE organization_id = ? AND is_active = 1", [$commitment['organization_id']]);
            foreach ($partnerUsers as $u) {
                Notification::send((int) $u['id'], 'Komitmen disetujui',
                    'Komitmen ' . $commitment['number'] . ' telah disetujui.', '/komitmen/' . $id);
            }
        }
        flash('success', __t('common.updated'));
        redirect('/komitmen/' . $id);
    }

    // ===== Internal =====

    private function generateNumber(): string
    {
        $year = date('Y');
        $count = (int) Database::scalar("SELECT COUNT(*) FROM commitments WHERE number LIKE ?", ["KOM-$year-%"]);
        return sprintf('KOM-%s-%03d', $year, $count + 1);
    }

    private function find(int $id): array
    {
        $commitment = Database::selectOne(
            "SELECT c.*, o.name AS org_name, p.name AS program_name, p.budget_needed,
                    ct.name AS contribution, fs.name AS funding_source, fy.year
             FROM commitments c
             JOIN organizations o ON o.id = c.organization_id
             JOIN programs p ON p.id = c.program_id
             JOIN fiscal_years fy ON fy.id = c.fiscal_year_id
             LEFT JOIN contribution_types ct ON ct.id = c.contribution_type_id
             LEFT JOIN funding_sources fs ON fs.id = c.funding_source_id
             WHERE c.id = ?", [$id]);
        if ($commitment === null) {
            http_response_code(404);
            exit(__t('common.not_found'));
        }
        if (Auth::role() === 'mitra' && (int) $commitment['organization_id'] !== (int) (Auth::user()['organization_id'] ?? 0)) {
            http_response_code(403);
            exit(__t('error.403'));
        }
        return $commitment;
    }

    private function refs(): array
    {
        return [
            'fiscal_years' => Database::select("SELECT id, year FROM fiscal_years WHERE is_active = 1 ORDER BY year DESC"),
            'organizations' => Database::select("SELECT id, name FROM organizations WHERE is_active = 1 ORDER BY name"),
            'programs' => Database::select(
                "SELECT p.id, p.code, p.name, p.budget_needed,
                        " . ProgramModel::COMMITTED_SQL . " AS committed
                 FROM programs p
                 WHERE p.status IN ('terverifikasi','dipublikasikan','dalam_penjajakan','komitmen_sebagian','dalam_pelaksanaan')
                 ORDER BY p.name"),
            'contribution_types' => Database::select("SELECT id, name FROM contribution_types WHERE is_active = 1 ORDER BY name"),
            'funding_sources' => Database::select("SELECT id, name FROM funding_sources WHERE is_active = 1 ORDER BY name"),
        ];
    }

    private function collect(): ?array
    {
        $orgId = (int) $this->input('organization_id', 0);
        $programId = (int) $this->input('program_id', 0);
        $yearId = (int) $this->input('fiscal_year_id', 0);
        $amount = (float) str_replace(['.', ','], ['', '.'], (string) $this->input('amount', '0'));
        if ($orgId === 0 || $programId === 0 || $yearId === 0 || $amount <= 0) {
            return null;
        }
        return [
            'organization_id' => $orgId,
            'program_id' => $programId,
            'fiscal_year_id' => $yearId,
            'amount' => $amount,
            'contribution_type_id' => $this->input('contribution_type_id') ?: null,
            'funding_source_id' => $this->input('funding_source_id') ?: null,
            'mou_number' => $this->input('mou_number') ?: null,
            'mou_date' => $this->input('mou_date') ?: null,
            'notes' => $this->input('notes') ?: null,
        ];
    }
}
