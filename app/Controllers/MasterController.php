<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\Database;
use App\Core\DataTable;

/**
 * CRUD generik untuk seluruh tabel master data sederhana.
 */
class MasterController extends Controller
{
    /** @return array<string, array{table: string, label: string, fields: array}> */
    private function config(): array
    {
        return [
            'tahun-anggaran' => [
                'table' => 'fiscal_years',
                'label' => __t('master.fiscal_years'),
                'fields' => [['name' => 'year', 'label' => __t('common.year'), 'type' => 'number']],
            ],
            'periode-laporan' => [
                'table' => 'reporting_periods',
                'label' => __t('master.reporting_periods'),
                'fields' => [
                    ['name' => 'name', 'label' => __t('common.name'), 'type' => 'text'],
                    ['name' => 'period_type', 'label' => 'Jenis', 'type' => 'select',
                     'options' => ['bulanan' => 'Bulanan', 'triwulanan' => 'Triwulanan', 'semesteran' => 'Semesteran', 'tahunan' => 'Tahunan']],
                ],
            ],
            'sumber-pendanaan' => [
                'table' => 'funding_sources',
                'label' => __t('master.funding_sources'),
                'fields' => [['name' => 'name', 'label' => __t('common.name'), 'type' => 'text']],
            ],
            'bentuk-kontribusi' => [
                'table' => 'contribution_types',
                'label' => __t('master.contribution_types'),
                'fields' => [['name' => 'name', 'label' => __t('common.name'), 'type' => 'text']],
            ],
            'bidang-program' => [
                'table' => 'program_fields',
                'label' => __t('master.program_fields'),
                'fields' => [['name' => 'name', 'label' => __t('common.name'), 'type' => 'text']],
            ],
            'bidang-usaha' => [
                'table' => 'business_sectors',
                'label' => __t('master.business_sectors'),
                'fields' => [['name' => 'name', 'label' => __t('common.name'), 'type' => 'text']],
            ],
            'jenis-badan-usaha' => [
                'table' => 'entity_types',
                'label' => __t('master.entity_types'),
                'fields' => [['name' => 'name', 'label' => __t('common.name'), 'type' => 'text']],
            ],
            'opd' => [
                'table' => 'departments',
                'label' => __t('master.departments'),
                'fields' => [
                    ['name' => 'name', 'label' => __t('common.name'), 'type' => 'text'],
                    ['name' => 'short_name', 'label' => 'Singkatan', 'type' => 'text', 'optional' => true],
                ],
            ],
            'kecamatan' => [
                'table' => 'districts',
                'label' => __t('master.districts'),
                'fields' => [['name' => 'name', 'label' => __t('common.name'), 'type' => 'text']],
            ],
            'desa' => [
                'table' => 'villages',
                'label' => __t('master.villages'),
                'fields' => [
                    ['name' => 'district_id', 'label' => __t('common.district'), 'type' => 'fk', 'ref' => 'districts'],
                    ['name' => 'name', 'label' => __t('common.name'), 'type' => 'text'],
                ],
            ],
        ];
    }

    private function resolve(string $key): array
    {
        $config = $this->config();
        if (!isset($config[$key])) {
            http_response_code(404);
            exit(__t('error.404'));
        }
        return $config[$key];
    }

    public function index(string $key): void
    {
        $cfg = $this->resolve($key);

        foreach ($cfg['fields'] as &$field) {
            if ($field['type'] === 'fk') {
                $field['options'] = array_column(
                    Database::select("SELECT id, name FROM `{$field['ref']}` WHERE is_active = 1 ORDER BY name"),
                    'name',
                    'id'
                );
            }
        }

        $this->render('master/index', [
            'title' => $cfg['label'],
            'key' => $key,
            'cfg' => $cfg,
            'masterMenus' => array_map(fn($c) => $c['label'], $this->config()),
        ]);
    }

    public function data(string $key): void
    {
        $cfg = $this->resolve($key);
        $table = $cfg['table'];
        $fields = $cfg['fields'];

        $fkJoins = '';
        $selectCols = [];
        $dtColumns = [null]; // kolom nomor: tidak dapat diurutkan
        foreach ($fields as $f) {
            if ($f['type'] === 'fk') {
                $fkJoins .= " LEFT JOIN `{$f['ref']}` ref_{$f['name']} ON ref_{$f['name']}.id = t.{$f['name']}";
                $selectCols[] = "ref_{$f['name']}.name AS {$f['name']}_label, t.{$f['name']}";
                $dtColumns[] = "ref_{$f['name']}.name";
            } else {
                $selectCols[] = "t.{$f['name']}";
                $dtColumns[] = "t.{$f['name']}";
            }
        }
        $dtColumns[] = 't.is_active';
        $dtColumns[] = null; // kolom aksi

        DataTable::respond(
            "SELECT t.id, t.is_active, " . implode(', ', $selectCols),
            "FROM `$table` t $fkJoins",
            $dtColumns,
            [],
            function (array $row, int $no) use ($fields): array {
                $cells = [$no];
                $editData = ['id' => $row['id']];
                foreach ($fields as $f) {
                    if ($f['type'] === 'fk') {
                        $cells[] = e($row[$f['name'] . '_label'] ?? '-');
                        $editData[$f['name']] = $row[$f['name']];
                    } elseif ($f['type'] === 'select') {
                        $cells[] = e($f['options'][$row[$f['name']]] ?? $row[$f['name']]);
                        $editData[$f['name']] = $row[$f['name']];
                    } else {
                        $cells[] = e($row[$f['name']]);
                        $editData[$f['name']] = $row[$f['name']];
                    }
                }
                $cells[] = $row['is_active']
                    ? '<span class="badge text-bg-success">' . __t('common.active') . '</span>'
                    : '<span class="badge text-bg-secondary">' . __t('common.inactive') . '</span>';

                $json = e(json_encode($editData, JSON_UNESCAPED_UNICODE));
                $toggleLabel = $row['is_active'] ? __t('common.deactivate') : __t('common.activate');
                $toggleIcon = $row['is_active'] ? 'bi-toggle-on' : 'bi-toggle-off';
                $cells[] = '<div class="text-end text-nowrap">'
                    . '<button class="btn btn-sm btn-outline-primary btn-edit me-1" data-row="' . $json . '" title="' . __t('common.edit') . '"><i class="bi bi-pencil"></i></button>'
                    . '<button class="btn btn-sm btn-outline-secondary btn-toggle" data-id="' . $row['id'] . '" title="' . $toggleLabel . '"><i class="bi ' . $toggleIcon . '"></i></button>'
                    . '</div>';
                return $cells;
            },
            'ORDER BY t.id DESC'
        );
    }

    public function store(string $key): void
    {
        $cfg = $this->resolve($key);
        $data = $this->collect($cfg);
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            redirect("/master/$key");
        }
        $id = Database::insert($cfg['table'], $data);
        Audit::log('create', $cfg['table'], $id, null, $data);
        flash('success', __t('common.saved'));
        redirect("/master/$key");
    }

    public function update(string $key, string $id): void
    {
        $cfg = $this->resolve($key);
        $before = Database::selectOne("SELECT * FROM `{$cfg['table']}` WHERE id = ?", [(int) $id]);
        if ($before === null) {
            flash('danger', __t('common.not_found'));
            redirect("/master/$key");
        }
        $data = $this->collect($cfg);
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            redirect("/master/$key");
        }
        Database::update($cfg['table'], $data, 'id = ?', [(int) $id]);
        Audit::log('update', $cfg['table'], (int) $id, $before, $data);
        flash('success', __t('common.updated'));
        redirect("/master/$key");
    }

    public function toggle(string $key, string $id): void
    {
        $cfg = $this->resolve($key);
        Database::execute("UPDATE `{$cfg['table']}` SET is_active = 1 - is_active WHERE id = ?", [(int) $id]);
        Audit::log('toggle', $cfg['table'], (int) $id);
        json_response(['ok' => true]);
    }

    private function collect(array $cfg): ?array
    {
        $data = [];
        foreach ($cfg['fields'] as $f) {
            $value = trim((string) ($_POST[$f['name']] ?? ''));
            if ($value === '' && empty($f['optional'])) {
                return null;
            }
            $data[$f['name']] = $value === '' ? null : $value;
        }
        return $data;
    }
}
