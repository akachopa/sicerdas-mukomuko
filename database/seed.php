<?php

declare(strict_types=1);

/**
 * Seeder SICERDAS Mukomuko.
 * Jalankan: php database/seed.php
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/Core/Database.php';

use App\Core\Database;

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $path = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($path)) {
            require $path;
        }
    }
});

$pdo = Database::pdo();

echo "Menjalankan schema.sql...\n";
$schema = file_get_contents(BASE_PATH . '/database/schema.sql');
// Buang komentar lalu eksekusi per pernyataan (PDO tidak mengizinkan multi-statement)
$schema = preg_replace('/^--.*$/m', '', $schema);
foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
    $pdo->exec($statement);
}

echo "Mengisi data master...\n";

// Roles
$roles = [
    ['super_admin', 'Super Administrator'],
    ['admin_bapperida', 'Administrator BAPPERIDA'],
    ['verifikator', 'Verifikator BAPPERIDA'],
    ['opd', 'OPD Pengusul'],
    ['mitra', 'Pihak Swasta / Mitra'],
    ['pimpinan', 'Pimpinan Daerah'],
];
foreach ($roles as $r) {
    Database::insert('roles', ['code' => $r[0], 'name' => $r[1]]);
}

// Tahun anggaran
foreach ([2024, 2025, 2026] as $y) {
    Database::insert('fiscal_years', ['year' => $y]);
}

// Periode laporan
$periods = [
    ['Triwulan I', 'triwulanan'], ['Triwulan II', 'triwulanan'],
    ['Triwulan III', 'triwulanan'], ['Triwulan IV', 'triwulanan'],
    ['Semester I', 'semesteran'], ['Semester II', 'semesteran'],
    ['Tahunan', 'tahunan'],
];
foreach ($periods as $p) {
    Database::insert('reporting_periods', ['name' => $p[0], 'period_type' => $p[1]]);
}

// Sumber pendanaan
$sources = [
    'Corporate Social Responsibility (CSR)', 'Tanggung Jawab Sosial dan Lingkungan (TJSL)',
    'Program Kemitraan dan Bina Lingkungan', 'Bantuan BUMN/BUMD',
    'Bantuan Lembaga Keuangan dan Perbankan', 'Dana Sosial Perusahaan',
    'Hibah Lembaga Nonprofit', 'Donasi Lembaga Filantropi', 'BAZNAS',
    'Dana Komunitas', 'Bantuan Yayasan', 'Bantuan Dunia Usaha',
    'Bantuan Barang dan Jasa', 'Kontribusi Tenaga Ahli', 'Sumber Non-APBD Lainnya',
];
foreach ($sources as $s) {
    Database::insert('funding_sources', ['name' => $s]);
}

// Bentuk kontribusi
$ctypes = [
    'Dana Tunai', 'Barang', 'Jasa', 'Pembangunan Fisik', 'Pelatihan', 'Beasiswa',
    'Peralatan', 'Pendampingan', 'Tenaga Ahli', 'Hibah Aset', 'Program Kemitraan',
    'Bantuan Operasional', 'Dukungan Teknologi',
];
foreach ($ctypes as $c) {
    Database::insert('contribution_types', ['name' => $c]);
}

// Bidang program
$fields = [
    'Lingkungan', 'Pemberdayaan Ekonomi Masyarakat', 'Kepemudaan', 'Karang Taruna',
    'Pendidikan', 'Sosial', 'Budaya', 'Keagamaan', 'Infrastruktur', 'Kesehatan',
    'Ketahanan Pangan', 'UMKM dan Ekonomi Kreatif', 'Sanitasi dan Air Bersih',
    'Penanggulangan Kemiskinan', 'Perlindungan Sosial', 'Penanggulangan Bencana',
    'Transformasi Digital', 'Pengembangan Desa', 'Perikanan dan Kelautan',
    'Pertanian dan Perkebunan',
];
foreach ($fields as $f) {
    Database::insert('program_fields', ['name' => $f]);
}

// Bidang usaha
$sectors = [
    'Perkebunan Kelapa Sawit', 'Pengolahan CPO', 'Pertambangan', 'Perbankan',
    'Perikanan', 'Perdagangan', 'Konstruksi', 'Telekomunikasi', 'Energi',
    'Transportasi dan Logistik', 'Kehutanan', 'Jasa Keuangan', 'Manufaktur', 'Lainnya',
];
foreach ($sectors as $s) {
    Database::insert('business_sectors', ['name' => $s]);
}

// Jenis badan usaha
$etypes = ['PT', 'CV', 'Koperasi', 'BUMN', 'BUMD', 'Yayasan', 'Firma', 'Perorangan', 'Lembaga Keuangan', 'Lainnya'];
foreach ($etypes as $t) {
    Database::insert('entity_types', ['name' => $t]);
}

// OPD
$departments = [
    ['Badan Perencanaan, Penelitian, dan Pengembangan Daerah', 'BAPPERIDA'],
    ['Dinas Pendidikan dan Kebudayaan', 'DISDIKBUD'],
    ['Dinas Kesehatan', 'DINKES'],
    ['Dinas Pekerjaan Umum dan Penataan Ruang', 'PUPR'],
    ['Dinas Sosial', 'DINSOS'],
    ['Dinas Lingkungan Hidup', 'DLH'],
    ['Dinas Pertanian', 'DISTAN'],
    ['Dinas Perikanan', 'DISKAN'],
    ['Dinas Koperasi, UKM, Perdagangan, dan Perindustrian', 'DISKOPERINDAG'],
    ['Dinas Pemberdayaan Masyarakat dan Desa', 'DPMD'],
];
foreach ($departments as $d) {
    Database::insert('departments', ['name' => $d[0], 'short_name' => $d[1]]);
}

// Kecamatan Kabupaten Mukomuko (15 kecamatan)
$districts = [
    'Kota Mukomuko', 'Air Dikit', 'XIV Koto', 'Lubuk Pinang', 'Air Manjuto',
    'V Koto', 'Selagan Raya', 'Teras Terunjam', 'Penarik', 'Pondok Suguh',
    'Sungai Rumbai', 'Teramang Jaya', 'Malin Deman', 'Ipuh', 'Air Rami',
];
$districtIds = [];
foreach ($districts as $d) {
    $districtIds[$d] = Database::insert('districts', ['name' => $d]);
}

// Contoh desa
$villages = [
    'Kota Mukomuko' => ['Kelurahan Bandar Ratu', 'Kelurahan Koto Jaya', 'Ujung Padang', 'Selagan Jaya'],
    'Penarik' => ['Penarik', 'Bukit Makmur', 'Marga Mulya', 'Lubuk Mukti'],
    'Ipuh' => ['Medan Jaya', 'Pasar Ipuh', 'Pulai Payung', 'Tirta Mulya'],
    'Lubuk Pinang' => ['Lubuk Pinang', 'Arah Tiga', 'Tanjung Jaya'],
    'Air Manjuto' => ['Pondok Makmur', 'Manjuto Jaya', 'Tirta Makmur'],
    'Teras Terunjam' => ['Teras Terunjam', 'Mekar Mulya', 'Terunjam Jaya'],
];
foreach ($villages as $district => $list) {
    foreach ($list as $v) {
        Database::insert('villages', ['district_id' => $districtIds[$district], 'name' => $v]);
    }
}

echo "Membuat pengguna demo...\n";

$hash = password_hash('password123', PASSWORD_BCRYPT);
$users = [
    ['role' => 1, 'name' => 'Super Administrator', 'email' => 'superadmin@mukomukokab.go.id', 'dept' => null, 'org' => null],
    ['role' => 2, 'name' => 'Admin BAPPERIDA', 'email' => 'admin@mukomukokab.go.id', 'dept' => 1, 'org' => null],
    ['role' => 3, 'name' => 'Verifikator BAPPERIDA', 'email' => 'verifikator@mukomukokab.go.id', 'dept' => 1, 'org' => null],
    ['role' => 6, 'name' => 'Bupati Mukomuko', 'email' => 'pimpinan@mukomukokab.go.id', 'dept' => null, 'org' => null],
];
foreach ($users as $u) {
    Database::insert('users', [
        'role_id' => $u['role'], 'full_name' => $u['name'], 'email' => $u['email'],
        'password' => $hash, 'department_id' => $u['dept'], 'organization_id' => $u['org'],
    ]);
}

// Akun OPD
Database::insert('users', [
    'role_id' => 4, 'full_name' => 'Operator Dinas Pendidikan', 'email' => 'disdik@mukomukokab.go.id',
    'password' => $hash, 'department_id' => 2,
]);
Database::insert('users', [
    'role_id' => 4, 'full_name' => 'Operator Dinas Kesehatan', 'email' => 'dinkes@mukomukokab.go.id',
    'password' => $hash, 'department_id' => 3,
]);

echo "Membuat data contoh perusahaan, program, komitmen, laporan...\n";

$orgs = [
    ['PT Agro Muko', 'PT Agro Muko Tbk', 1, 1, 'Penarik', 5000000000, 'aktif'],
    ['PT Daria Dharma Pratama', 'PT Daria Dharma Pratama', 1, 2, 'Ipuh', 3000000000, 'aktif'],
    ['Bank Bengkulu Cabang Mukomuko', 'PT BPD Bengkulu', 9, 4, 'Kota Mukomuko', 1500000000, 'sudah_melapor'],
    ['PT Sapta Sentosa Jaya Abadi', 'PT Sapta Sentosa Jaya Abadi', 1, 1, 'Lubuk Pinang', 2000000000, 'belum_melapor'],
    ['PT Usaha Sawit Mandiri', 'PT Usaha Sawit Mandiri', 1, 1, 'Teras Terunjam', 1000000000, 'belum_melapor'],
];
$orgIds = [];
foreach ($orgs as $i => $o) {
    $orgIds[] = Database::insert('organizations', [
        'name' => $o[0], 'legal_name' => $o[1], 'entity_type_id' => $o[2],
        'business_sector_id' => $o[3], 'district_id' => $districtIds[$o[4]],
        'csr_potential' => $o[5], 'compliance_status' => $o[6],
        'email' => 'csr' . ($i + 1) . '@perusahaan.co.id', 'phone' => '0812345678' . $i,
        'pic_name' => 'PIC CSR ' . $o[0], 'address' => 'Kecamatan ' . $o[4] . ', Kabupaten Mukomuko',
    ]);
}

// Akun mitra
Database::insert('users', [
    'role_id' => 5, 'full_name' => 'CSR Officer Agro Muko', 'email' => 'mitra@agromuko.co.id',
    'password' => $hash, 'organization_id' => $orgIds[0],
]);
Database::insert('users', [
    'role_id' => 5, 'full_name' => 'CSR Officer Bank Bengkulu', 'email' => 'mitra@bankbengkulu.co.id',
    'password' => $hash, 'organization_id' => $orgIds[2],
]);

$programs = [
    ['PRG-2026-001', 'Rehabilitasi Ruang Kelas SDN 05 Penarik', 3, 2, 5, 'Penarik', 450000000, 'dipublikasikan', 'tinggi', 320],
    ['PRG-2026-002', 'Pembangunan Sarana Air Bersih Desa Medan Jaya', 3, 4, 13, 'Ipuh', 750000000, 'dipublikasikan', 'mendesak', 1200],
    ['PRG-2026-003', 'Pelatihan UMKM Pengolahan Hasil Perikanan', 3, 9, 12, 'Kota Mukomuko', 250000000, 'dipublikasikan', 'sedang', 150],
    ['PRG-2026-004', 'Penghijauan Kawasan Pesisir Air Rami', 3, 6, 1, 'Air Rami', 300000000, 'terverifikasi', 'tinggi', 500],
    ['PRG-2026-005', 'Posyandu Terintegrasi Desa Bukit Makmur', 3, 3, 10, 'Penarik', 200000000, 'menunggu_verifikasi', 'sedang', 400],
    ['PRG-2026-006', 'Beasiswa Anak Berprestasi Keluarga Prasejahtera', 3, 2, 5, 'Kota Mukomuko', 500000000, 'dalam_pelaksanaan', 'tinggi', 100],
];
$progIds = [];
foreach ($programs as $p) {
    $progIds[] = Database::insert('programs', [
        'code' => $p[0], 'name' => $p[1], 'fiscal_year_id' => $p[2], 'department_id' => $p[3],
        'program_field_id' => $p[4], 'district_id' => $districtIds[$p[5]],
        'budget_needed' => $p[6], 'status' => $p[7], 'priority_level' => $p[8],
        'beneficiary_count' => $p[9],
        'is_published' => in_array($p[7], ['dipublikasikan', 'dalam_pelaksanaan'], true) ? 1 : 0,
        'description' => 'Program prioritas daerah: ' . $p[1] . '.',
        'objective' => 'Meningkatkan kesejahteraan masyarakat Kabupaten Mukomuko.',
        'output' => 'Terlaksananya ' . strtolower($p[1]) . '.',
        'indicator' => 'Jumlah penerima manfaat mencapai target.',
        'beneficiary_target' => 'Masyarakat Kecamatan ' . $p[5],
        'created_by' => 5,
    ]);
}

$commitments = [
    ['KOM-2026-001', 3, $orgIds[0], $progIds[0], 450000000, 1, 1, 'aktif'],
    ['KOM-2026-002', 3, $orgIds[2], $progIds[2], 150000000, 5, 5, 'disetujui'],
    ['KOM-2026-003', 3, $orgIds[1], $progIds[5], 500000000, 6, 1, 'direalisasikan_sebagian'],
    ['KOM-2026-004', 3, $orgIds[0], $progIds[1], 300000000, 4, 1, 'dalam_pembahasan'],
];
$comIds = [];
foreach ($commitments as $c) {
    $comIds[] = Database::insert('commitments', [
        'number' => $c[0], 'fiscal_year_id' => $c[1], 'organization_id' => $c[2],
        'program_id' => $c[3], 'amount' => $c[4], 'contribution_type_id' => $c[5],
        'funding_source_id' => $c[6], 'status' => $c[7], 'created_by' => 2,
    ]);
}

Database::insert('realizations', [
    'number' => 'REA-2026-001', 'commitment_id' => $comIds[2], 'realization_date' => '2026-03-15',
    'stage' => 'Tahap Pertama', 'amount' => 250000000, 'status' => 'terverifikasi',
    'description' => 'Penyaluran beasiswa semester ganjil untuk 50 siswa.', 'beneficiary_count' => 50, 'created_by' => 2,
]);
Database::insert('realizations', [
    'number' => 'REA-2026-002', 'commitment_id' => $comIds[0], 'realization_date' => '2026-04-20',
    'stage' => 'Tahap Pertama', 'amount' => 200000000, 'status' => 'dikirim',
    'description' => 'Pekerjaan rehabilitasi tahap pertama: atap dan dinding.', 'beneficiary_count' => 320, 'created_by' => 2,
]);

// Laporan CSR contoh
$repId = Database::insert('csr_reports', [
    'number' => 'LAP-2026-001', 'organization_id' => $orgIds[2], 'fiscal_year_id' => 3,
    'reporting_period_id' => 1, 'responsible_name' => 'CSR Officer Bank Bengkulu',
    'responsible_position' => 'Kepala Cabang', 'status' => 'terverifikasi',
    'registration_number' => 'REG-2026-0001',
    'submitted_at' => '2026-04-05 10:00:00', 'verified_at' => '2026-04-10 14:00:00',
    'verified_by' => 3, 'created_by' => 8,
]);
Database::insert('csr_report_items', [
    'csr_report_id' => $repId, 'activity_name' => 'Pelatihan Literasi Keuangan UMKM',
    'program_field_id' => 12, 'district_id' => $districtIds['Kota Mukomuko'],
    'planned_amount' => 100000000, 'realized_amount' => 95000000,
    'funding_source_id' => 5, 'contribution_type_id' => 5,
    'benefit' => 'Peningkatan kemampuan pengelolaan keuangan 80 pelaku UMKM.',
    'beneficiary_count' => 80, 'beneficiary_type' => 'Pelaku UMKM',
    'start_date' => '2026-02-01', 'end_date' => '2026-02-28',
]);

$repId2 = Database::insert('csr_reports', [
    'number' => 'LAP-2026-002', 'organization_id' => $orgIds[0], 'fiscal_year_id' => 3,
    'reporting_period_id' => 1, 'responsible_name' => 'CSR Officer Agro Muko',
    'responsible_position' => 'Manajer CSR', 'status' => 'dikirim',
    'submitted_at' => '2026-04-12 09:00:00', 'created_by' => 7,
]);
Database::insert('csr_report_items', [
    'csr_report_id' => $repId2, 'activity_name' => 'Bantuan Sembako Ramadan',
    'program_field_id' => 6, 'district_id' => $districtIds['Penarik'],
    'planned_amount' => 75000000, 'realized_amount' => 75000000,
    'funding_source_id' => 1, 'contribution_type_id' => 2,
    'benefit' => 'Bantuan sembako untuk 500 keluarga prasejahtera.',
    'beneficiary_count' => 500, 'beneficiary_type' => 'Keluarga Prasejahtera',
    'start_date' => '2026-03-10', 'end_date' => '2026-03-20',
]);

echo "Selesai. Akun demo (password: password123):\n";
echo "  superadmin@mukomukokab.go.id  (Super Admin)\n";
echo "  admin@mukomukokab.go.id       (Admin BAPPERIDA)\n";
echo "  verifikator@mukomukokab.go.id (Verifikator)\n";
echo "  disdik@mukomukokab.go.id      (OPD)\n";
echo "  mitra@agromuko.co.id          (Mitra/Perusahaan)\n";
echo "  pimpinan@mukomukokab.go.id    (Pimpinan)\n";
