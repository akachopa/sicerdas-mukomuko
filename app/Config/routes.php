<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\MasterController;
use App\Controllers\OrganizationController;
use App\Controllers\ProgramController;
use App\Controllers\CatalogController;
use App\Controllers\CommitmentController;
use App\Controllers\RealizationController;
use App\Controllers\ReportController;
use App\Controllers\UserController;
use App\Controllers\AuditController;
use App\Controllers\NotificationController;
use App\Controllers\RecapController;
use App\Controllers\PublicController;

/** @var App\Core\Router $router */

// Publik
$router->get('/', [PublicController::class, 'landing']);
$router->get('/katalog', [PublicController::class, 'catalog']);
$router->get('/katalog/{id}', [PublicController::class, 'programDetail']);
$router->get('/lang/{lang}', [AuthController::class, 'setLang']);

// Autentikasi
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout'], ['auth']);

// Dasbor
$router->get('/dashboard', [DashboardController::class, 'index'], ['auth']);
$router->get('/dashboard/charts', [DashboardController::class, 'charts'], ['auth']);

// Master data (super_admin & admin)
$adminOnly = ['auth', 'role:super_admin,admin_bapperida'];
$router->get('/master/{key}', [MasterController::class, 'index'], $adminOnly);
$router->get('/master/{key}/data', [MasterController::class, 'data'], $adminOnly);
$router->post('/master/{key}/store', [MasterController::class, 'store'], $adminOnly);
$router->post('/master/{key}/update/{id}', [MasterController::class, 'update'], $adminOnly);
$router->post('/master/{key}/toggle/{id}', [MasterController::class, 'toggle'], $adminOnly);

// Pihak swasta / mitra
$router->get('/organisasi', [OrganizationController::class, 'index'], $adminOnly);
$router->get('/organisasi/data', [OrganizationController::class, 'data'], $adminOnly);
$router->get('/organisasi/tambah', [OrganizationController::class, 'create'], $adminOnly);
$router->post('/organisasi/simpan', [OrganizationController::class, 'store'], $adminOnly);
$router->get('/organisasi/{id}', [OrganizationController::class, 'show'], ['auth', 'role:super_admin,admin_bapperida,verifikator,pimpinan']);
$router->get('/organisasi/{id}/ubah', [OrganizationController::class, 'edit'], $adminOnly);
$router->post('/organisasi/{id}/update', [OrganizationController::class, 'update'], $adminOnly);
$router->post('/organisasi/{id}/toggle', [OrganizationController::class, 'toggle'], $adminOnly);
$router->post('/organisasi/{id}/dokumen', [OrganizationController::class, 'uploadDocument'], $adminOnly);

// Profil perusahaan (mitra)
$router->get('/profil-perusahaan', [OrganizationController::class, 'myProfile'], ['auth', 'role:mitra']);
$router->post('/profil-perusahaan/update', [OrganizationController::class, 'updateMyProfile'], ['auth', 'role:mitra']);
$router->post('/profil-perusahaan/dokumen', [OrganizationController::class, 'uploadMyDocument'], ['auth', 'role:mitra']);

// Program prioritas
$programManage = ['auth', 'role:super_admin,admin_bapperida,opd'];
$programView = ['auth', 'role:super_admin,admin_bapperida,verifikator,opd,pimpinan'];
$router->get('/program', [ProgramController::class, 'index'], $programView);
$router->get('/program/data', [ProgramController::class, 'data'], $programView);
$router->get('/program/tambah', [ProgramController::class, 'create'], $programManage);
$router->post('/program/simpan', [ProgramController::class, 'store'], $programManage);
$router->get('/program/{id}', [ProgramController::class, 'show'], ['auth']);
$router->get('/program/{id}/ubah', [ProgramController::class, 'edit'], $programManage);
$router->post('/program/{id}/update', [ProgramController::class, 'update'], $programManage);
$router->post('/program/{id}/ajukan', [ProgramController::class, 'submit'], $programManage);
$router->post('/program/{id}/verifikasi', [ProgramController::class, 'verify'], ['auth', 'role:super_admin,admin_bapperida,verifikator']);
$router->post('/program/{id}/publikasi', [ProgramController::class, 'publish'], $adminOnly);
$router->post('/program/{id}/dokumen', [ProgramController::class, 'uploadDocument'], $programManage);

// Katalog internal untuk mitra + minat
$router->get('/mitra/katalog', [CatalogController::class, 'index'], ['auth', 'role:mitra']);
$router->post('/mitra/katalog/{id}/minat', [CatalogController::class, 'interest'], ['auth', 'role:mitra']);

// Komitmen
$commitView = ['auth', 'role:super_admin,admin_bapperida,verifikator,pimpinan,mitra'];
$router->get('/komitmen', [CommitmentController::class, 'index'], $commitView);
$router->get('/komitmen/data', [CommitmentController::class, 'data'], $commitView);
$router->get('/komitmen/tambah', [CommitmentController::class, 'create'], $adminOnly);
$router->post('/komitmen/simpan', [CommitmentController::class, 'store'], $adminOnly);
$router->get('/komitmen/{id}', [CommitmentController::class, 'show'], $commitView);
$router->get('/komitmen/{id}/ubah', [CommitmentController::class, 'edit'], $adminOnly);
$router->post('/komitmen/{id}/update', [CommitmentController::class, 'update'], $adminOnly);
$router->post('/komitmen/{id}/status', [CommitmentController::class, 'changeStatus'], $adminOnly);

// Realisasi
$router->get('/realisasi', [RealizationController::class, 'index'], $commitView);
$router->get('/realisasi/data', [RealizationController::class, 'data'], $commitView);
$router->get('/realisasi/tambah', [RealizationController::class, 'create'], ['auth', 'role:super_admin,admin_bapperida,mitra']);
$router->post('/realisasi/simpan', [RealizationController::class, 'store'], ['auth', 'role:super_admin,admin_bapperida,mitra']);
$router->post('/realisasi/{id}/verifikasi', [RealizationController::class, 'verify'], ['auth', 'role:super_admin,admin_bapperida,verifikator']);

// Laporan CSR
$reportView = ['auth', 'role:super_admin,admin_bapperida,verifikator,opd,pimpinan,mitra'];
$router->get('/laporan', [ReportController::class, 'index'], $reportView);
$router->get('/laporan/data', [ReportController::class, 'data'], $reportView);
$router->get('/laporan/tambah', [ReportController::class, 'create'], ['auth', 'role:super_admin,admin_bapperida,mitra']);
$router->post('/laporan/simpan', [ReportController::class, 'store'], ['auth', 'role:super_admin,admin_bapperida,mitra']);
$router->get('/laporan/{id}', [ReportController::class, 'show'], $reportView);
$router->get('/laporan/{id}/ubah', [ReportController::class, 'edit'], ['auth', 'role:super_admin,admin_bapperida,mitra']);
$router->post('/laporan/{id}/update', [ReportController::class, 'update'], ['auth', 'role:super_admin,admin_bapperida,mitra']);
$router->post('/laporan/{id}/kirim', [ReportController::class, 'submit'], ['auth', 'role:super_admin,admin_bapperida,mitra']);
$router->post('/laporan/{id}/verifikasi', [ReportController::class, 'verify'], ['auth', 'role:super_admin,admin_bapperida,verifikator']);
$router->post('/laporan/{id}/dokumen', [ReportController::class, 'uploadDocument'], ['auth', 'role:super_admin,admin_bapperida,mitra']);
$router->get('/laporan/{id}/cetak', [ReportController::class, 'printView'], $reportView);

// Pengguna (super admin & admin)
$router->get('/pengguna', [UserController::class, 'index'], $adminOnly);
$router->get('/pengguna/data', [UserController::class, 'data'], $adminOnly);
$router->get('/pengguna/tambah', [UserController::class, 'create'], $adminOnly);
$router->post('/pengguna/simpan', [UserController::class, 'store'], $adminOnly);
$router->get('/pengguna/{id}/ubah', [UserController::class, 'edit'], $adminOnly);
$router->post('/pengguna/{id}/update', [UserController::class, 'update'], $adminOnly);
$router->post('/pengguna/{id}/toggle', [UserController::class, 'toggle'], $adminOnly);

// Audit log
$router->get('/audit', [AuditController::class, 'index'], ['auth', 'role:super_admin,admin_bapperida']);
$router->get('/audit/data', [AuditController::class, 'data'], ['auth', 'role:super_admin,admin_bapperida']);

// Notifikasi
$router->get('/notifikasi', [NotificationController::class, 'index'], ['auth']);
$router->post('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead'], ['auth']);
$router->get('/notifikasi/{id}/buka', [NotificationController::class, 'open'], ['auth']);

// Rekap dan ekspor
$recapRoles = ['auth', 'role:super_admin,admin_bapperida,verifikator,pimpinan'];
$router->get('/rekap', [RecapController::class, 'index'], $recapRoles);
$router->get('/rekap/excel', [RecapController::class, 'exportExcel'], $recapRoles);
$router->get('/rekap/cetak', [RecapController::class, 'printView'], $recapRoles);
