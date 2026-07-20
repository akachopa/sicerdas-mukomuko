<?php

declare(strict_types=1);

use App\Core\Lang;

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function __t(string $key, array $replace = []): string
{
    return Lang::get($key, $replace);
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function json_response(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

/** @return array<int, array{type: string, message: string}> */
function get_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function old(string $key, mixed $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function format_rupiah(float|int|string|null $value): string
{
    return 'Rp ' . number_format((float) ($value ?? 0), 0, ',', '.');
}

function format_date(?string $date): string
{
    if (!$date) {
        return '-';
    }
    $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $ts = strtotime($date);
    return date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

function status_badge(string $status, ?string $label = null): string
{
    $label = $label ?? __t('status.' . $status);
    $map = [
        // hijau
        'selesai' => 'success', 'terverifikasi' => 'success', 'disetujui' => 'success',
        'aktif' => 'success', 'direalisasikan_penuh' => 'success', 'dipublikasikan' => 'success',
        // kuning
        'menunggu_verifikasi' => 'warning', 'diajukan' => 'warning', 'dikirim' => 'warning',
        'perlu_revisi' => 'warning', 'perlu_perbaikan' => 'warning', 'dalam_pembahasan' => 'warning',
        'dalam_penjajakan' => 'warning', 'menunggu_laporan' => 'warning', 'revisi_dikirim' => 'warning',
        'direalisasikan_sebagian' => 'warning', 'komitmen_sebagian' => 'warning',
        // merah
        'ditolak' => 'danger', 'dibatalkan' => 'danger', 'kedaluwarsa' => 'danger', 'terlambat' => 'danger',
        'belum_melapor' => 'danger', 'ditangguhkan' => 'danger',
        // kepatuhan
        'sudah_melapor' => 'success', 'perlu_tindak_lanjut' => 'warning',
        'profil_belum_lengkap' => 'warning', 'terdaftar' => 'info',
        // biru
        'dalam_pelaksanaan' => 'info', 'komitmen_penuh' => 'info', 'ditawarkan' => 'info', 'dikunci' => 'info',
        // abu-abu
        'draft' => 'secondary', 'ditunda' => 'secondary', 'nonaktif' => 'secondary',
    ];
    $color = $map[$status] ?? 'secondary';
    return '<span class="badge text-bg-' . $color . '">' . e($label) . '</span>';
}
