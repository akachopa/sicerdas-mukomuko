<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\DataTable;
use App\Core\Upload;
use App\Models\ProgramModel;

class RealizationController extends Controller
{
    public function index(): void
    {
        $this->render('realizations/index', [
            'title' => __t('realization.title'),
            'canAdd' => in_array(Auth::role(), ['super_admin', 'admin_bapperida', 'mitra'], true),
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

        $canVerify = in_array(Auth::role(), ['super_admin', 'admin_bapperida', 'verifikator'], true);
        DataTable::respond(
            "SELECT r.id, r.number, r.realization_date, r.stage, r.amount, r.status, r.evidence_path,
                    o.name AS org_name, p.name AS program_name, c.number AS commitment_number, c.id AS commitment_id",
            "FROM realizations r
             JOIN commitments c ON c.id = r.commitment_id
             JOIN organizations o ON o.id = c.organization_id
             JOIN programs p ON p.id = c.program_id" . $where,
            [null, 'r.number', 'r.realization_date', 'o.name', 'p.name', 'r.amount', 'r.status', null],
            $params,
            function (array $row, int $no) use ($canVerify): array {
                $actions = '<div class="text-end text-nowrap">'
                    . '<a class="btn btn-sm btn-outline-secondary" href="/komitmen/' . $row['commitment_id'] . '" title="' . __t('common.detail') . '"><i class="bi bi-eye"></i></a>';
                if ($row['evidence_path']) {
                    $actions .= ' <a class="btn btn-sm btn-outline-secondary" href="' . e($row['evidence_path']) . '" target="_blank" title="' . __t('realization.evidence') . '"><i class="bi bi-paperclip"></i></a>';
                }
                if ($canVerify && $row['status'] === 'dikirim') {
                    $actions .= ' <button class="btn btn-sm btn-outline-success btn-verify" data-id="' . $row['id'] . '" title="' . __t('realization.verify') . '"><i class="bi bi-check-lg"></i></button>';
                }
                $actions .= '</div>';
                return [
                    $no,
                    '<span class="text-muted small">' . e($row['number']) . '</span><br><small class="text-muted">' . e($row['commitment_number']) . '</small>',
                    format_date($row['realization_date']),
                    '<strong>' . e($row['org_name']) . '</strong>',
                    e($row['program_name']) . ($row['stage'] ? '<br><small class="text-muted">' . e($row['stage']) . '</small>' : ''),
                    '<div class="text-end">' . format_rupiah($row['amount']) . '</div>',
                    status_badge($row['status']),
                    $actions,
                ];
            },
            'ORDER BY r.id DESC'
        );
    }

    public function create(): void
    {
        $where = "WHERE c.status IN ('disetujui','aktif','direalisasikan_sebagian')";
        $params = [];
        if (Auth::role() === 'mitra') {
            $where .= " AND c.organization_id = ?";
            $params[] = (int) (Auth::user()['organization_id'] ?? 0);
        }
        $this->render('realizations/form', [
            'title' => __t('realization.add'),
            'commitments' => Database::select(
                "SELECT c.id, c.number, c.amount, o.name AS org_name, p.name AS program_name,
                        (SELECT COALESCE(SUM(rz.amount),0) FROM realizations rz
                         WHERE rz.commitment_id = c.id AND rz.status IN ('dikirim','terverifikasi')) AS realized
                 FROM commitments c
                 JOIN organizations o ON o.id = c.organization_id
                 JOIN programs p ON p.id = c.program_id
                 $where ORDER BY c.number", $params),
            'preselect' => (int) $this->input('commitment', 0),
        ]);
    }

    public function store(): void
    {
        $commitmentId = (int) $this->input('commitment_id', 0);
        $amount = (float) str_replace(['.', ','], ['', '.'], (string) $this->input('amount', '0'));
        $date = (string) $this->input('realization_date', '');

        $commitment = Database::selectOne("SELECT * FROM commitments WHERE id = ?", [$commitmentId]);
        if ($commitment === null || $amount <= 0 || $date === '') {
            flash('danger', __t('common.required_fields'));
            redirect('/realisasi/tambah');
        }
        if (Auth::role() === 'mitra' && (int) $commitment['organization_id'] !== (int) (Auth::user()['organization_id'] ?? 0)) {
            http_response_code(403);
            exit(__t('error.403'));
        }

        $realized = (float) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM realizations WHERE commitment_id = ? AND status IN ('dikirim','terverifikasi')",
            [$commitmentId]
        );
        if ($realized + $amount > (float) $commitment['amount']) {
            flash('danger', __t('realization.exceeds'));
            redirect('/realisasi/tambah?commitment=' . $commitmentId);
        }

        $evidence = null;
        try {
            $evidence = Upload::handle($_FILES['evidence'] ?? [], 'realisasi');
        } catch (\RuntimeException $e) {
            flash('danger', $e->getMessage());
            redirect('/realisasi/tambah?commitment=' . $commitmentId);
        }

        $year = date('Y');
        $count = (int) Database::scalar("SELECT COUNT(*) FROM realizations WHERE number LIKE ?", ["REA-$year-%"]);
        $id = Database::insert('realizations', [
            'number' => sprintf('REA-%s-%03d', $year, $count + 1),
            'commitment_id' => $commitmentId,
            'realization_date' => $date,
            'stage' => $this->input('stage') ?: null,
            'amount' => $amount,
            'description' => $this->input('description') ?: null,
            'beneficiary_count' => (int) $this->input('beneficiary_count', 0),
            'evidence_path' => $evidence['path'] ?? null,
            'evidence_name' => $evidence['name'] ?? null,
            'status' => 'dikirim',
            'created_by' => Auth::id(),
        ]);
        Audit::log('create', 'realizations', $id);
        flash('success', __t('common.saved'));
        redirect('/komitmen/' . $commitmentId);
    }

    public function verify(string $id): void
    {
        $realization = Database::selectOne("SELECT * FROM realizations WHERE id = ?", [(int) $id]);
        if ($realization === null) {
            json_response(['ok' => false], 404);
        }
        Database::update('realizations', ['status' => 'terverifikasi'], 'id = ?', [(int) $id]);
        Audit::log('verify', 'realizations', (int) $id);

        // Perbarui status komitmen berdasarkan capaian realisasi
        $commitment = Database::selectOne("SELECT * FROM commitments WHERE id = ?", [$realization['commitment_id']]);
        if ($commitment !== null) {
            $realized = (float) Database::scalar(
                "SELECT COALESCE(SUM(amount),0) FROM realizations WHERE commitment_id = ? AND status = 'terverifikasi'",
                [$commitment['id']]
            );
            $newStatus = $realized >= (float) $commitment['amount'] ? 'direalisasikan_penuh' : 'direalisasikan_sebagian';
            if (in_array($commitment['status'], ['disetujui', 'aktif', 'direalisasikan_sebagian'], true)) {
                Database::update('commitments', ['status' => $newStatus], 'id = ?', [$commitment['id']]);
            }
            ProgramModel::refreshFundingStatus((int) $commitment['program_id']);
        }
        json_response(['ok' => true]);
    }
}
