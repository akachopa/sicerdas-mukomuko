<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class RecapController extends Controller
{
    public function index(): void
    {
        [$data, $filters] = $this->buildData();
        $this->render('recap/index', [
            'title' => __t('recap.title'),
            'data' => $data,
            'filters' => $filters,
            'years' => Database::select("SELECT id, year FROM fiscal_years ORDER BY year DESC"),
            'periods' => Database::select("SELECT id, name FROM reporting_periods ORDER BY id"),
        ]);
    }

    public function exportExcel(): void
    {
        [$data, $filters] = $this->buildData();
        Audit::log('export', 'recap', null, null, ['type' => $filters['type'], 'format' => 'xls']);

        $filename = 'rekap-' . $filters['type'] . '-' . date('Ymd-His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Format SpreadsheetML sederhana yang dapat dibuka Excel/LibreOffice
        echo "<html xmlns:x=\"urn:schemas-microsoft-com:office:excel\"><head><meta charset=\"utf-8\"></head><body>";
        echo '<table border="1"><tr><th colspan="' . count($data['headers']) . '">SICERDAS Mukomuko — ' . e($data['title']) . '</th></tr>';
        echo '<tr><td colspan="' . count($data['headers']) . '">' . __t('recap.printed_at') . ': ' . date('d/m/Y H:i')
            . ' — ' . __t('recap.printed_by') . ': ' . e(Auth::user()['full_name']) . '</td></tr>';
        echo '<tr>';
        foreach ($data['headers'] as $h) {
            echo '<th style="background:#0f2a52;color:#fff">' . e($h) . '</th>';
        }
        echo '</tr>';
        foreach ($data['rows'] as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . e((string) $cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table></body></html>';
        exit;
    }

    public function printView(): void
    {
        [$data, $filters] = $this->buildData();
        Audit::log('export', 'recap', null, null, ['type' => $filters['type'], 'format' => 'pdf']);
        $this->render('recap/print', [
            'title' => $data['title'],
            'data' => $data,
        ], 'layouts/print');
    }

    /** @return array{0: array, 1: array} */
    private function buildData(): array
    {
        $type = (string) $this->input('type', 'perusahaan');
        $yearId = (int) $this->input('year', 0);
        $periodId = (int) $this->input('period', 0);

        $where = "WHERE r.status IN ('terverifikasi','dikunci','selesai','dikirim','sedang_diperiksa','revisi_dikirim')";
        $params = [];
        if ($yearId > 0) {
            $where .= " AND r.fiscal_year_id = ?";
            $params[] = $yearId;
        }
        if ($periodId > 0) {
            $where .= " AND r.reporting_period_id = ?";
            $params[] = $periodId;
        }

        $data = match ($type) {
            'bidang' => [
                'title' => __t('recap.by_field'),
                'headers' => [__t('common.no'), __t('program.field'), __t('recap.activity_count'), __t('report.planned'), __t('report.realized'), __t('dash.beneficiaries')],
                'rows' => $this->numberRows(Database::select(
                    "SELECT pf.name, COUNT(i.id) AS cnt, SUM(i.planned_amount) AS planned,
                            SUM(i.realized_amount) AS realized, SUM(i.beneficiary_count) AS beneficiaries
                     FROM csr_report_items i
                     JOIN csr_reports r ON r.id = i.csr_report_id
                     JOIN program_fields pf ON pf.id = i.program_field_id
                     $where GROUP BY pf.id, pf.name ORDER BY realized DESC", $params), true),
            ],
            'kecamatan' => [
                'title' => __t('recap.by_district'),
                'headers' => [__t('common.no'), __t('common.district'), __t('recap.activity_count'), __t('report.planned'), __t('report.realized'), __t('dash.beneficiaries')],
                'rows' => $this->numberRows(Database::select(
                    "SELECT d.name, COUNT(i.id) AS cnt, SUM(i.planned_amount) AS planned,
                            SUM(i.realized_amount) AS realized, SUM(i.beneficiary_count) AS beneficiaries
                     FROM csr_report_items i
                     JOIN csr_reports r ON r.id = i.csr_report_id
                     JOIN districts d ON d.id = i.district_id
                     $where GROUP BY d.id, d.name ORDER BY realized DESC", $params), true),
            ],
            default => [
                'title' => __t('recap.by_org'),
                'headers' => [__t('common.no'), __t('report.organization'), __t('recap.report_count'), __t('recap.activity_count'), __t('report.planned'), __t('report.realized'), __t('dash.beneficiaries')],
                'rows' => $this->numberRows(Database::select(
                    "SELECT o.name, COUNT(DISTINCT r.id) AS reports, COUNT(i.id) AS cnt,
                            SUM(i.planned_amount) AS planned, SUM(i.realized_amount) AS realized,
                            SUM(i.beneficiary_count) AS beneficiaries
                     FROM csr_reports r
                     JOIN organizations o ON o.id = r.organization_id
                     LEFT JOIN csr_report_items i ON i.csr_report_id = r.id
                     $where GROUP BY o.id, o.name ORDER BY realized DESC", $params), false),
            ],
        };

        return [$data, ['type' => $type, 'year' => $yearId, 'period' => $periodId]];
    }

    private function numberRows(array $rows, bool $simple): array
    {
        $out = [];
        $no = 1;
        foreach ($rows as $row) {
            if ($simple) {
                $out[] = [
                    $no++,
                    $row['name'],
                    (int) $row['cnt'],
                    format_rupiah($row['planned']),
                    format_rupiah($row['realized']),
                    number_format((int) $row['beneficiaries'], 0, ',', '.'),
                ];
            } else {
                $out[] = [
                    $no++,
                    $row['name'],
                    (int) $row['reports'],
                    (int) $row['cnt'],
                    format_rupiah($row['planned']),
                    format_rupiah($row['realized']),
                    number_format((int) $row['beneficiaries'], 0, ',', '.'),
                ];
            }
        }
        return $out;
    }
}
