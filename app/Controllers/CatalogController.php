<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Notification;
use App\Models\ProgramModel;

class CatalogController extends Controller
{
    public function index(): void
    {
        $fieldId = (int) $this->input('field', 0);
        $districtId = (int) $this->input('district', 0);

        $where = "WHERE p.is_published = 1 AND p.status IN ('dipublikasikan','dalam_penjajakan','komitmen_sebagian','dalam_pelaksanaan')";
        $params = [];
        if ($fieldId > 0) {
            $where .= " AND p.program_field_id = ?";
            $params[] = $fieldId;
        }
        if ($districtId > 0) {
            $where .= " AND p.district_id = ?";
            $params[] = $districtId;
        }

        $programs = Database::select(
            "SELECT p.*, pf.name AS field_name, dist.name AS district_name, d.short_name AS dept_short,
                    " . ProgramModel::COMMITTED_SQL . " AS committed
             FROM programs p
             JOIN program_fields pf ON pf.id = p.program_field_id
             JOIN departments d ON d.id = p.department_id
             LEFT JOIN districts dist ON dist.id = p.district_id
             $where
             ORDER BY FIELD(p.priority_level,'mendesak','tinggi','sedang','rendah'), p.id DESC",
            $params
        );

        $orgId = (int) (Auth::user()['organization_id'] ?? 0);
        $myInterests = array_column(
            Database::select("SELECT program_id FROM program_interests WHERE organization_id = ?", [$orgId]),
            'program_id'
        );

        $this->render('catalog/index', [
            'title' => __t('menu.catalog'),
            'programs' => $programs,
            'myInterests' => $myInterests,
            'fields' => Database::select("SELECT id, name FROM program_fields WHERE is_active = 1 ORDER BY name"),
            'districts' => Database::select("SELECT id, name FROM districts WHERE is_active = 1 ORDER BY name"),
            'filterField' => $fieldId,
            'filterDistrict' => $districtId,
        ]);
    }

    public function interest(string $id): void
    {
        $orgId = (int) (Auth::user()['organization_id'] ?? 0);
        $program = Database::selectOne("SELECT * FROM programs WHERE id = ? AND is_published = 1", [(int) $id]);
        if ($program === null || $orgId === 0) {
            flash('danger', __t('common.not_found'));
            redirect('/mitra/katalog');
        }

        $exists = Database::scalar(
            "SELECT COUNT(*) FROM program_interests WHERE program_id = ? AND organization_id = ? AND interest_type = 'minat'",
            [(int) $id, $orgId]
        );
        if (!$exists) {
            Database::insert('program_interests', [
                'program_id' => (int) $id,
                'organization_id' => $orgId,
                'interest_type' => 'minat',
                'message' => $this->input('message') ?: null,
            ]);
            Audit::log('interest', 'program_interests', (int) $id);
            $orgName = (string) Database::scalar("SELECT name FROM organizations WHERE id = ?", [$orgId]);
            Notification::sendToRole('admin_bapperida', 'Minat program baru',
                $orgName . ' menyatakan minat pada program "' . $program['name'] . '".', '/program/' . $id);
        }
        flash('success', __t('program.interest_sent'));
        redirect('/mitra/katalog');
    }
}
