<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Pemroses server-side untuk DataTables.
 *
 * $columns: daftar ekspresi kolom SQL yang dapat diurutkan/dicari,
 * urutannya harus sama dengan kolom orderable di sisi klien
 * (kolom nomor dan aksi dikecualikan lewat orderable:false di klien).
 */
class DataTable
{
    /**
     * @param string   $baseQuery   Query tanpa ORDER BY/LIMIT, contoh: "FROM users u JOIN roles r ..."
     * @param string   $select      Bagian SELECT
     * @param string[] $columns     Ekspresi kolom SQL sesuai indeks kolom klien (null = tidak dapat diurut/dicari)
     * @param array    $params      Parameter bind untuk kondisi WHERE pada baseQuery
     * @param callable $rowMapper   fn(array $row, int $rowNumber): array — memetakan baris ke array kolom untuk klien
     */
    public static function respond(
        string $select,
        string $baseQuery,
        array $columns,
        array $params,
        callable $rowMapper,
        string $defaultOrder = ''
    ): never {
        $draw = (int) ($_GET['draw'] ?? $_POST['draw'] ?? 1);
        $start = max(0, (int) ($_GET['start'] ?? $_POST['start'] ?? 0));
        $length = (int) ($_GET['length'] ?? $_POST['length'] ?? 10);
        if ($length < 1 || $length > 200) {
            $length = 10;
        }
        $search = trim((string) ($_GET['search']['value'] ?? $_POST['search']['value'] ?? ''));

        $searchable = array_values(array_filter($columns));

        $where = '';
        $searchParams = [];
        if ($search !== '' && $searchable !== []) {
            $like = [];
            foreach ($searchable as $col) {
                $like[] = "$col LIKE ?";
                $searchParams[] = '%' . $search . '%';
            }
            $where = (str_contains(strtoupper($baseQuery), 'WHERE') ? ' AND (' : ' WHERE (') . implode(' OR ', $like) . ')';
        }

        $totalRecords = (int) Database::scalar("SELECT COUNT(*) $baseQuery", $params);
        $filteredRecords = $where === ''
            ? $totalRecords
            : (int) Database::scalar("SELECT COUNT(*) $baseQuery $where", [...$params, ...$searchParams]);

        $orderSql = $defaultOrder;
        $orderReq = $_GET['order'] ?? $_POST['order'] ?? [];
        if (!empty($orderReq)) {
            $parts = [];
            foreach ($orderReq as $o) {
                $idx = (int) ($o['column'] ?? -1);
                $dir = strtolower((string) ($o['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
                if (isset($columns[$idx]) && $columns[$idx] !== null) {
                    $parts[] = $columns[$idx] . ' ' . $dir;
                }
            }
            if ($parts !== []) {
                $orderSql = 'ORDER BY ' . implode(', ', $parts);
            }
        }

        $sql = "$select $baseQuery $where $orderSql LIMIT $length OFFSET $start";
        $rows = Database::select($sql, [...$params, ...$searchParams]);

        $data = [];
        $no = $start + 1;
        foreach ($rows as $row) {
            $data[] = $rowMapper($row, $no++);
        }

        json_response([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }
}
