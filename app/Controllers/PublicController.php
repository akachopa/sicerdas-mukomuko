<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\ProgramModel;

class PublicController extends Controller
{
    public function landing(): void
    {
        $this->render('public/landing', [
            'title' => __t('public.hero_title'),
            'stats' => $this->stats(),
            'featured' => $this->publishedPrograms(limit: 6),
            'partners' => Database::select(
                "SELECT DISTINCT o.name FROM organizations o
                 JOIN commitments c ON c.organization_id = o.id
                 WHERE c.status IN ('disetujui','aktif','direalisasikan_sebagian','direalisasikan_penuh','selesai')
                 ORDER BY o.name LIMIT 12"),
            'byField' => Database::select(
                "SELECT pf.name, COUNT(*) AS total FROM programs p
                 JOIN program_fields pf ON pf.id = p.program_field_id
                 WHERE p.is_published = 1 GROUP BY pf.id, pf.name ORDER BY total DESC LIMIT 6"),
        ], 'layouts/public');
    }

    public function catalog(): void
    {
        $fieldId = (int) $this->input('field', 0);
        $search = (string) $this->input('q', '');

        $this->render('public/catalog', [
            'title' => __t('public.catalog_title'),
            'programs' => $this->publishedPrograms($fieldId, $search),
            'fields' => Database::select("SELECT id, name FROM program_fields WHERE is_active = 1 ORDER BY name"),
            'filterField' => $fieldId,
            'search' => $search,
        ], 'layouts/public');
    }

    public function programDetail(string $id): void
    {
        $program = Database::selectOne(
            "SELECT p.*, pf.name AS field_name, d.short_name AS dept_short, d.name AS dept_name,
                    dist.name AS district_name, v.name AS village_name, fy.year,
                    " . ProgramModel::COMMITTED_SQL . " AS committed
             FROM programs p
             JOIN program_fields pf ON pf.id = p.program_field_id
             JOIN departments d ON d.id = p.department_id
             JOIN fiscal_years fy ON fy.id = p.fiscal_year_id
             LEFT JOIN districts dist ON dist.id = p.district_id
             LEFT JOIN villages v ON v.id = p.village_id
             WHERE p.id = ? AND p.is_published = 1", [(int) $id]);
        if ($program === null) {
            http_response_code(404);
            $this->render('errors/404', ['title' => '404'], 'layouts/public');
            return;
        }
        $this->render('public/program_detail', [
            'title' => $program['name'],
            'program' => $program,
            'supporters' => Database::select(
                "SELECT DISTINCT o.name FROM organizations o
                 JOIN commitments c ON c.organization_id = o.id
                 WHERE c.program_id = ? AND c.status IN ('disetujui','aktif','direalisasikan_sebagian','direalisasikan_penuh','selesai')",
                [(int) $id]),
        ], 'layouts/public');
    }

    private function stats(): array
    {
        return [
            'contribution' => (float) Database::scalar(
                "SELECT COALESCE(SUM(amount),0) FROM realizations WHERE status = 'terverifikasi'"),
            'programs' => (int) Database::scalar("SELECT COUNT(*) FROM programs WHERE is_published = 1"),
            'partners' => (int) Database::scalar(
                "SELECT COUNT(DISTINCT organization_id) FROM commitments
                 WHERE status IN ('disetujui','aktif','direalisasikan_sebagian','direalisasikan_penuh','selesai')"),
            'beneficiaries' => (int) Database::scalar(
                "SELECT COALESCE(SUM(i.beneficiary_count),0) FROM csr_report_items i
                 JOIN csr_reports r ON r.id = i.csr_report_id
                 WHERE r.status IN ('terverifikasi','dikunci','selesai')"),
        ];
    }

    private function publishedPrograms(int $fieldId = 0, string $search = '', int $limit = 60): array
    {
        $where = "WHERE p.is_published = 1";
        $params = [];
        if ($fieldId > 0) {
            $where .= " AND p.program_field_id = ?";
            $params[] = $fieldId;
        }
        if ($search !== '') {
            $where .= " AND p.name LIKE ?";
            $params[] = '%' . $search . '%';
        }
        return Database::select(
            "SELECT p.id, p.name, p.description, p.budget_needed, p.priority_level, p.status,
                    p.beneficiary_count, pf.name AS field_name, dist.name AS district_name,
                    " . ProgramModel::COMMITTED_SQL . " AS committed
             FROM programs p
             JOIN program_fields pf ON pf.id = p.program_field_id
             LEFT JOIN districts dist ON dist.id = p.district_id
             $where
             ORDER BY FIELD(p.priority_level,'mendesak','tinggi','sedang','rendah'), p.id DESC
             LIMIT $limit", $params);
    }
}
