<?php

declare(strict_types=1);

namespace App\Core;

class Upload
{
    private const ALLOWED = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private const MAX_SIZE = 10 * 1024 * 1024; // 10 MB

    /**
     * @param array $file elemen dari $_FILES
     * @return array{path: string, name: string, size: int, mime: string}|null null jika tidak ada file
     * @throws \RuntimeException jika file tidak valid
     */
    public static function handle(array $file, string $subdir): ?array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(__t('upload.failed'));
        }
        if ($file['size'] > self::MAX_SIZE) {
            throw new \RuntimeException(__t('upload.too_large'));
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED[$ext])) {
            throw new \RuntimeException(__t('upload.invalid_type'));
        }

        $mime = mime_content_type($file['tmp_name']) ?: '';
        if ($mime !== self::ALLOWED[$ext]) {
            throw new \RuntimeException(__t('upload.invalid_type'));
        }

        $dir = BASE_PATH . '/public/uploads/' . $subdir;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            throw new \RuntimeException(__t('upload.failed'));
        }

        return [
            'path' => '/uploads/' . $subdir . '/' . $name,
            'name' => $file['name'],
            'size' => (int) $file['size'],
            'mime' => $mime,
        ];
    }
}
