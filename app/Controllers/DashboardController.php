<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\ProgramModel;

class DashboardController extends Controller
{
    public function index(): void
    {
        $role = Auth::role();
        match ($role) {
            'mitra' => $this->partnerDashboard(),
            'opd' => $this->opdDashboard(),
            default => $this->adminDashboard(),
        };
    }

    private function adminDashboard(): void
    {
        $kpi = [
            'orgs' => (int) Database::scalar("SELECT COUNT(*) FROM organizations WHERE is_active = 1"),
            'orgs_reported' => (int) Database::scalar("SELECT COUNT(*) FROM organizations WHERE compliance_status = 'sudah_melapor'"),
            'orgs_not_reported' => (int) Database::scalar("SELECT COUNT(*) FROM organizations WHERE compliance_status = 'belum_melapor'"),
            'programs' => (int) Database::scalar("SELECT COUNT(*) FROM programs WHERE status NOT IN ('draft','dibatalkan')"),
            'programs_unfunded' => (int) Database::scalar(
                "SELECT COUNT(*) FROM programs p
                 WHERE p.status IN ('dipublikasikan','terverifikasi')
                   AND " . ProgramModel::COMMITTED_SQL . " = 0"),
            'total_needed' => (float) Database::scalar("SELECT COALESCE(SUM(budget_needed),0) FROM programs WHERE status NOT IN ('draft','dibatalkan','ditunda')"),
            'total_commitment' => (float) Database::scalar(
                "SELECT COALESCE(SUM(amount),0) FROM commitments
                 WHERE status IN ('disetujui','aktif','direalisasikan_sebagian','direalisasikan_penuh','selesai')"),
            'total_realization' => (float) Database::scalar(
                "SELECT COALESCE(SUM(amount),0) FROM realizations WHERE status = 'terverifikasi'"),
            'pending_reports' => (int) Database::scalar(
                "SELECT COUNT(*) FROM csr_reports WHERE status IN ('dikirim','revisi_dikirim','sedang_diperiksa')"),
            'beneficiaries' => (int) Database::scalar(
                "SELECT COALESCE(SUM(i.beneficiary_count),0) FROM csr_report_items i
                 JOIN csr_reports r ON r.id = i.csr_report_id
                 WHERE r.status IN ('terverifikasi','dikunci','selesai')"),
        ];
        $kpi['realization_pct'] = $kpi['total_commitment'] > 0
            ? round($kpi['total_realization'] / $kpi['total_commitment'] * 100, 1) : 0;

        $this->render('dashboard/admin', [
            'title' => __t('common.dashboard'),
            'kpi' => $kpi,
            'latestReports' => Database::select(
                "SELECT r.id, r.number, r.status, o.name AS org_name, r.submitted_at
                 FROM csr_reports r JOIN organizations o ON o.id = r.organization_id
                 WHERE r.status != 'draft' ORDER BY r.submitted_at DESC LIMIT 8"),
            'urgentPrograms' => Database::select(
                "SELECT p.id, p.name, p.budget_needed, p.priority_level, p.status,
                        " . ProgramModel::COMMITTED_SQL . " AS committed
                 FROM programs p
                 WHERE p.priority_level IN ('mendesak','tinggi')
                   AND p.status IN ('dipublikasikan','terverifikasi','komitmen_sebagian')
                 ORDER BY FIELD(p.priority_level,'mendesak','tinggi'), p.id DESC LIMIT 8"),
            'notReported' => Database::select(
                "SELECT id, name, phone, pic_name FROM organizations
                 WHERE compliance_status = 'belum_melapor' AND is_active = 1 LIMIT 8"),
        ]);
    }

    private function partnerDashboard(): void
    {
        $orgId = (int) (Auth::user()['organization_id'] ?? 0);
        $org = Database::selectOne("SELECT * FROM organizations WHERE id = ?", [$orgId]);

        // Kelengkapan profil sederhana
        $profileFields = ['legal_name', 'entity_type_id', 'business_sector_id', 'nib', 'npwp', 'address', 'district_id', 'email', 'phone', 'pic_name'];
        $filled = 0;
        foreach ($profileFields as $f) {
            if (!empty($org[$f])) {
                $filled++;
            }
        }

        $this->render('dashboard/partner', [
            'title' => __t('common.dashboard'),
            'org' => $org,
            'profilePct' => (int) round($filled / count($profileFields) * 100),
            'kpi' => [
                'commitment' => (float) Database::scalar(
                    "SELECT COALESCE(SUM(amount),0) FROM commitments
                     WHERE organization_id = ? AND status IN ('disetujui','aktif','direalisasikan_sebagian','direalisasikan_penuh','selesai')", [$orgId]),
                'realization' => (float) Database::scalar(
                    "SELECT COALESCE(SUM(rz.amount),0) FROM realizations rz
                     JOIN commitments c ON c.id = rz.commitment_id
                     WHERE c.organization_id = ? AND rz.status = 'terverifikasi'", [$orgId]),
                'running' => (int) Database::scalar(
                    "SELECT COUNT(*) FROM commitments WHERE organization_id = ? AND status IN ('disetujui','aktif','direalisasikan_sebagian')", [$orgId]),
                'need_revision' => (int) Database::scalar(
                    "SELECT COUNT(*) FROM csr_reports WHERE organization_id = ? AND status = 'perlu_perbaikan'", [$orgId]),
            ],
            'myReports' => Database::select(
                "SELECT r.id, r.number, r.status, fy.year, rp.name AS period
                 FROM csr_reports r
                 JOIN fiscal_years fy ON fy.id = r.fiscal_year_id
                 JOIN reporting_periods rp ON rp.id = r.reporting_period_id
                 WHERE r.organization_id = ? ORDER BY r.id DESC LIMIT 6", [$orgId]),
            'recommended' => Database::select(
                "SELECT p.id, p.name, p.budget_needed, pf.name AS field_name,
                        " . ProgramModel::COMMITTED_SQL . " AS committed
                 FROM programs p JOIN program_fields pf ON pf.id = p.program_field_id
                 WHERE p.is_published = 1 AND p.status IN ('dipublikasikan','komitmen_sebagian')
                 ORDER BY FIELD(p.priority_level,'mendesak','tinggi','sedang','rendah') LIMIT 4"),
        ]);
    }

    private function opdDashboard(): void
    {
        $deptId = (int) (Auth::user()['department_id'] ?? 0);
        $this->render('dashboard/opd', [
            'title' => __t('common.dashboard'),
            'kpi' => [
                'submitted' => (int) Database::scalar("SELECT COUNT(*) FROM programs WHERE department_id = ?", [$deptId]),
                'verified' => (int) Database::scalar(
                    "SELECT COUNT(*) FROM programs WHERE department_id = ?
                     AND status IN ('terverifikasi','dipublikasikan','komitmen_sebagian','komitmen_penuh','dalam_pelaksanaan','selesai')", [$deptId]),
                'needed' => (float) Database::scalar(
                    "SELECT COALESCE(SUM(budget_needed),0) FROM programs WHERE department_id = ? AND status NOT IN ('draft','dibatalkan')", [$deptId]),
                'committed' => (float) Database::scalar(
                    "SELECT COALESCE(SUM(c.amount),0) FROM commitments c
                     JOIN programs p ON p.id = c.program_id
                     WHERE p.department_id = ? AND c.status IN ('disetujui','aktif','direalisasikan_sebagian','direalisasikan_penuh','selesai')", [$deptId]),
            ],
            'myPrograms' => Database::select(
                "SELECT p.id, p.code, p.name, p.status, p.budget_needed
                 FROM programs p WHERE p.department_id = ? ORDER BY p.id DESC LIMIT 10", [$deptId]),
        ]);
    }

    /** Data JSON untuk grafik dashboard. */
    public function charts(): void
    {
        $monthly = Database::select(
            "SELECT m.month,
                    COALESCE(c.total, 0) AS commitment,
                    COALESCE(r.total, 0) AS realization
             FROM (SELECT 1 month UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
                   UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) m
             LEFT JOIN (SELECT MONTH(created_at) mo, SUM(amount) total FROM commitments
                        WHERE YEAR(created_at) = YEAR(CURDATE())
                          AND status IN ('disetujui','aktif','direalisasikan_sebagian','direalisasikan_penuh','selesai')
                        GROUP BY MONTH(created_at)) c ON c.mo = m.month
             LEFT JOIN (SELECT MONTH(realization_date) mo, SUM(amount) total FROM realizations
                        WHERE YEAR(realization_date) = YEAR(CURDATE()) AND status = 'terverifikasi'
                        GROUP BY MONTH(realization_date)) r ON r.mo = m.month
             ORDER BY m.month");

        $byField = Database::select(
            "SELECT pf.name, COALESCE(SUM(i.realized_amount),0) AS total
             FROM csr_report_items i
             JOIN program_fields pf ON pf.id = i.program_field_id
             JOIN csr_reports r ON r.id = i.csr_report_id
             WHERE r.status IN ('terverifikasi','dikunci','selesai')
             GROUP BY pf.id, pf.name ORDER BY total DESC LIMIT 8");

        $byStatus = Database::select(
            "SELECT status, COUNT(*) AS total FROM programs
             WHERE status NOT IN ('draft') GROUP BY status ORDER BY total DESC");

        json_response([
            'monthly' => $monthly,
            'byField' => $byField,
            'byStatus' => array_map(fn($row) => [
                'label' => __t('status.' . $row['status']),
                'total' => (int) $row['total'],
            ], $byStatus),
        ]);
    }
}
