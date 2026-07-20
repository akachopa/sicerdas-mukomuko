-- Skema database SICERDAS Mukomuko
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS audit_logs, notifications, verification_notes, csr_report_documents,
    csr_report_items, csr_reports, realizations, commitments, program_interests,
    program_documents, programs, organization_documents, organizations,
    villages, districts, departments, entity_types, business_sectors,
    program_fields, funding_sources, contribution_types, reporting_periods,
    fiscal_years, users, roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ========== Autentikasi & Pengguna ==========

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    organization_id INT UNSIGNED NULL,
    department_id INT UNSIGNED NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    position VARCHAR(100) NULL,
    nip VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== Master Data ==========

CREATE TABLE fiscal_years (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    year SMALLINT UNSIGNED NOT NULL UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reporting_periods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    period_type ENUM('bulanan','triwulanan','semesteran','tahunan') NOT NULL DEFAULT 'triwulanan',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE funding_sources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contribution_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE program_fields (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE business_sectors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE entity_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    short_name VARCHAR(50) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE districts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE villages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    district_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_villages_district FOREIGN KEY (district_id) REFERENCES districts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== Pihak Swasta / Mitra ==========

CREATE TABLE organizations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    legal_name VARCHAR(200) NULL,
    entity_type_id INT UNSIGNED NULL,
    business_sector_id INT UNSIGNED NULL,
    nib VARCHAR(50) NULL,
    npwp VARCHAR(50) NULL,
    address TEXT NULL,
    district_id INT UNSIGNED NULL,
    village_id INT UNSIGNED NULL,
    website VARCHAR(200) NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(30) NULL,
    pic_name VARCHAR(150) NULL,
    pic_position VARCHAR(100) NULL,
    pic_phone VARCHAR(30) NULL,
    pic_email VARCHAR(150) NULL,
    employee_count INT UNSIGNED NULL,
    local_employee_count INT UNSIGNED NULL,
    csr_potential DECIMAL(18,2) NULL,
    established_year SMALLINT UNSIGNED NULL,
    notes TEXT NULL,
    compliance_status ENUM('terdaftar','profil_belum_lengkap','aktif','nonaktif','sudah_melapor','belum_melapor','perlu_tindak_lanjut','terverifikasi','ditangguhkan') NOT NULL DEFAULT 'terdaftar',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_org_entity_type FOREIGN KEY (entity_type_id) REFERENCES entity_types(id),
    CONSTRAINT fk_org_sector FOREIGN KEY (business_sector_id) REFERENCES business_sectors(id),
    CONSTRAINT fk_org_district FOREIGN KEY (district_id) REFERENCES districts(id),
    CONSTRAINT fk_org_village FOREIGN KEY (village_id) REFERENCES villages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
    ADD CONSTRAINT fk_users_org FOREIGN KEY (organization_id) REFERENCES organizations(id),
    ADD CONSTRAINT fk_users_dept FOREIGN KEY (department_id) REFERENCES departments(id);

CREATE TABLE organization_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id INT UNSIGNED NOT NULL,
    doc_type VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    uploaded_by INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orgdoc_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== Program Prioritas ==========

CREATE TABLE programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    fiscal_year_id INT UNSIGNED NOT NULL,
    department_id INT UNSIGNED NOT NULL,
    program_field_id INT UNSIGNED NOT NULL,
    description TEXT NULL,
    background TEXT NULL,
    objective TEXT NULL,
    district_id INT UNSIGNED NULL,
    village_id INT UNSIGNED NULL,
    location_detail VARCHAR(255) NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    beneficiary_target VARCHAR(255) NULL,
    beneficiary_count INT UNSIGNED NULL DEFAULT 0,
    budget_needed DECIMAL(18,2) NOT NULL DEFAULT 0,
    output TEXT NULL,
    outcome TEXT NULL,
    indicator TEXT NULL,
    priority_level ENUM('rendah','sedang','tinggi','mendesak') NOT NULL DEFAULT 'sedang',
    start_date DATE NULL,
    end_date DATE NULL,
    status ENUM('draft','diajukan','menunggu_verifikasi','perlu_revisi','terverifikasi','dipublikasikan','dalam_penjajakan','komitmen_sebagian','komitmen_penuh','dalam_pelaksanaan','menunggu_laporan','selesai','ditunda','dibatalkan') NOT NULL DEFAULT 'draft',
    revision_note TEXT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_prog_year FOREIGN KEY (fiscal_year_id) REFERENCES fiscal_years(id),
    CONSTRAINT fk_prog_dept FOREIGN KEY (department_id) REFERENCES departments(id),
    CONSTRAINT fk_prog_field FOREIGN KEY (program_field_id) REFERENCES program_fields(id),
    CONSTRAINT fk_prog_district FOREIGN KEY (district_id) REFERENCES districts(id),
    CONSTRAINT fk_prog_village FOREIGN KEY (village_id) REFERENCES villages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE program_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    doc_type VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    uploaded_by INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_progdoc_prog FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE program_interests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    organization_id INT UNSIGNED NOT NULL,
    interest_type ENUM('minat','dukungan_penuh','dukungan_sebagian','pertemuan') NOT NULL DEFAULT 'minat',
    message TEXT NULL,
    status ENUM('diajukan','ditindaklanjuti','diterima','ditolak','dibatalkan') NOT NULL DEFAULT 'diajukan',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_interest (program_id, organization_id, interest_type),
    CONSTRAINT fk_int_prog FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    CONSTRAINT fk_int_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== Komitmen & Realisasi ==========

CREATE TABLE commitments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(50) NOT NULL UNIQUE,
    fiscal_year_id INT UNSIGNED NOT NULL,
    organization_id INT UNSIGNED NOT NULL,
    program_id INT UNSIGNED NOT NULL,
    amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    contribution_type_id INT UNSIGNED NULL,
    funding_source_id INT UNSIGNED NULL,
    mou_number VARCHAR(100) NULL,
    mou_date DATE NULL,
    notes TEXT NULL,
    status ENUM('draft','diajukan','dalam_pembahasan','disetujui','aktif','direalisasikan_sebagian','direalisasikan_penuh','selesai','dibatalkan','kedaluwarsa') NOT NULL DEFAULT 'draft',
    created_by INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_com_year FOREIGN KEY (fiscal_year_id) REFERENCES fiscal_years(id),
    CONSTRAINT fk_com_org FOREIGN KEY (organization_id) REFERENCES organizations(id),
    CONSTRAINT fk_com_prog FOREIGN KEY (program_id) REFERENCES programs(id),
    CONSTRAINT fk_com_ctype FOREIGN KEY (contribution_type_id) REFERENCES contribution_types(id),
    CONSTRAINT fk_com_fsource FOREIGN KEY (funding_source_id) REFERENCES funding_sources(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE realizations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(50) NOT NULL UNIQUE,
    commitment_id INT UNSIGNED NOT NULL,
    realization_date DATE NOT NULL,
    stage VARCHAR(100) NULL,
    amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    description TEXT NULL,
    beneficiary_count INT UNSIGNED NULL DEFAULT 0,
    evidence_path VARCHAR(255) NULL,
    evidence_name VARCHAR(255) NULL,
    status ENUM('draft','dikirim','terverifikasi','ditolak') NOT NULL DEFAULT 'dikirim',
    created_by INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_real_com FOREIGN KEY (commitment_id) REFERENCES commitments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== Laporan CSR ==========

CREATE TABLE csr_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(50) NOT NULL UNIQUE,
    registration_number VARCHAR(50) NULL,
    organization_id INT UNSIGNED NOT NULL,
    fiscal_year_id INT UNSIGNED NOT NULL,
    reporting_period_id INT UNSIGNED NOT NULL,
    responsible_name VARCHAR(150) NULL,
    responsible_position VARCHAR(100) NULL,
    notes TEXT NULL,
    status ENUM('draft','dikirim','sedang_diperiksa','perlu_perbaikan','revisi_dikirim','terverifikasi','ditolak','dikunci','selesai') NOT NULL DEFAULT 'draft',
    submitted_at DATETIME NULL,
    verified_at DATETIME NULL,
    verified_by INT UNSIGNED NULL,
    locked_at DATETIME NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_rep_org FOREIGN KEY (organization_id) REFERENCES organizations(id),
    CONSTRAINT fk_rep_year FOREIGN KEY (fiscal_year_id) REFERENCES fiscal_years(id),
    CONSTRAINT fk_rep_period FOREIGN KEY (reporting_period_id) REFERENCES reporting_periods(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE csr_report_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    csr_report_id INT UNSIGNED NOT NULL,
    program_id INT UNSIGNED NULL,
    activity_name VARCHAR(255) NOT NULL,
    program_field_id INT UNSIGNED NULL,
    district_id INT UNSIGNED NULL,
    village_id INT UNSIGNED NULL,
    location_detail VARCHAR(255) NULL,
    planned_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    realized_amount DECIMAL(18,2) NOT NULL DEFAULT 0,
    funding_source_id INT UNSIGNED NULL,
    contribution_type_id INT UNSIGNED NULL,
    benefit TEXT NULL,
    beneficiary_count INT UNSIGNED NULL DEFAULT 0,
    beneficiary_type VARCHAR(150) NULL,
    obstacles TEXT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_item_report FOREIGN KEY (csr_report_id) REFERENCES csr_reports(id) ON DELETE CASCADE,
    CONSTRAINT fk_item_prog FOREIGN KEY (program_id) REFERENCES programs(id),
    CONSTRAINT fk_item_field FOREIGN KEY (program_field_id) REFERENCES program_fields(id),
    CONSTRAINT fk_item_district FOREIGN KEY (district_id) REFERENCES districts(id),
    CONSTRAINT fk_item_village FOREIGN KEY (village_id) REFERENCES villages(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE csr_report_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    csr_report_id INT UNSIGNED NOT NULL,
    doc_type VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(100) NULL,
    uploaded_by INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_repdoc_report FOREIGN KEY (csr_report_id) REFERENCES csr_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE verification_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    csr_report_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    note_type ENUM('catatan','revisi','persetujuan','penolakan') NOT NULL DEFAULT 'catatan',
    note TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vnote_report FOREIGN KEY (csr_report_id) REFERENCES csr_reports(id) ON DELETE CASCADE,
    CONSTRAINT fk_vnote_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== Notifikasi & Audit ==========

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    url VARCHAR(255) NOT NULL DEFAULT '#',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_notif_user (user_id, is_read),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    user_name VARCHAR(150) NULL,
    action VARCHAR(50) NOT NULL,
    module VARCHAR(50) NOT NULL,
    record_id INT UNSIGNED NULL,
    data_before JSON NULL,
    data_after JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_audit_module (module, record_id),
    KEY idx_audit_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
