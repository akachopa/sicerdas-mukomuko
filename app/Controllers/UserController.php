<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\Database;
use App\Core\DataTable;

class UserController extends Controller
{
    public function index(): void
    {
        $this->render('users/index', ['title' => __t('user.title')]);
    }

    public function data(): void
    {
        DataTable::respond(
            "SELECT u.id, u.full_name, u.email, u.is_active, u.last_login_at,
                    r.name AS role_name, o.name AS org_name, d.short_name AS dept_name",
            "FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN organizations o ON o.id = u.organization_id
             LEFT JOIN departments d ON d.id = u.department_id",
            [null, 'u.full_name', 'u.email', 'r.name', 'u.last_login_at', 'u.is_active', null],
            [],
            function (array $row, int $no): array {
                $unit = $row['org_name'] ?? $row['dept_name'];
                $toggleLabel = $row['is_active'] ? __t('common.deactivate') : __t('common.activate');
                return [
                    $no,
                    '<strong>' . e($row['full_name']) . '</strong>' . ($unit ? '<br><small class="text-muted">' . e($unit) . '</small>' : ''),
                    e($row['email']),
                    e($row['role_name']),
                    $row['last_login_at'] ? format_date($row['last_login_at']) : '-',
                    $row['is_active']
                        ? '<span class="badge text-bg-success">' . __t('common.active') . '</span>'
                        : '<span class="badge text-bg-secondary">' . __t('common.inactive') . '</span>',
                    '<div class="text-end text-nowrap">'
                    . '<a class="btn btn-sm btn-outline-primary me-1" href="/pengguna/' . $row['id'] . '/ubah" title="' . __t('common.edit') . '"><i class="bi bi-pencil"></i></a>'
                    . '<button class="btn btn-sm btn-outline-secondary btn-toggle" data-id="' . $row['id'] . '" title="' . $toggleLabel . '"><i class="bi ' . ($row['is_active'] ? 'bi-toggle-on' : 'bi-toggle-off') . '"></i></button>'
                    . '</div>',
                ];
            },
            'ORDER BY u.id DESC'
        );
    }

    public function create(): void
    {
        $this->render('users/form', ['title' => __t('user.add'), 'user' => null, 'refs' => $this->refs()]);
    }

    public function store(): void
    {
        $data = $this->collect(true);
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            redirect('/pengguna/tambah');
        }
        if (Database::scalar("SELECT COUNT(*) FROM users WHERE email = ?", [$data['email']]) > 0) {
            flash('danger', 'Email sudah terdaftar.');
            redirect('/pengguna/tambah');
        }
        $id = Database::insert('users', $data);
        Audit::log('create', 'users', $id, null, ['email' => $data['email'], 'role_id' => $data['role_id']]);
        flash('success', __t('common.saved'));
        redirect('/pengguna');
    }

    public function edit(string $id): void
    {
        $user = Database::selectOne("SELECT * FROM users WHERE id = ?", [(int) $id]);
        if ($user === null) {
            flash('danger', __t('common.not_found'));
            redirect('/pengguna');
        }
        $this->render('users/form', ['title' => __t('user.edit'), 'user' => $user, 'refs' => $this->refs()]);
    }

    public function update(string $id): void
    {
        $data = $this->collect(false);
        if ($data === null) {
            flash('danger', __t('common.required_fields'));
            redirect('/pengguna/' . $id . '/ubah');
        }
        Database::update('users', $data, 'id = ?', [(int) $id]);
        Audit::log('update', 'users', (int) $id, null, ['email' => $data['email'], 'role_id' => $data['role_id']]);
        flash('success', __t('common.updated'));
        redirect('/pengguna');
    }

    public function toggle(string $id): void
    {
        Database::execute("UPDATE users SET is_active = 1 - is_active WHERE id = ?", [(int) $id]);
        Audit::log('toggle', 'users', (int) $id);
        json_response(['ok' => true]);
    }

    private function refs(): array
    {
        return [
            'roles' => Database::select("SELECT id, name FROM roles ORDER BY id"),
            'organizations' => Database::select("SELECT id, name FROM organizations WHERE is_active = 1 ORDER BY name"),
            'departments' => Database::select("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name"),
        ];
    }

    private function collect(bool $passwordRequired): ?array
    {
        $name = (string) $this->input('full_name', '');
        $email = (string) $this->input('email', '');
        $roleId = (int) $this->input('role_id', 0);
        $password = (string) $this->input('password', '');

        if ($name === '' || $email === '' || $roleId === 0 || ($passwordRequired && strlen($password) < 8)) {
            return null;
        }

        $data = [
            'full_name' => $name,
            'email' => $email,
            'role_id' => $roleId,
            'phone' => $this->input('phone') ?: null,
            'position' => $this->input('position') ?: null,
            'nip' => $this->input('nip') ?: null,
            'organization_id' => $this->input('organization_id') ?: null,
            'department_id' => $this->input('department_id') ?: null,
        ];
        if ($password !== '') {
            if (strlen($password) < 8) {
                return null;
            }
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }
        return $data;
    }
}
