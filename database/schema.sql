-- ============================================================
-- BLCM (Bridge Law Case Management) — Full Database Schema
-- Combines MRMS (28 tables) + Commission (13 tables)
-- ============================================================

CREATE DATABASE IF NOT EXISTS blcm_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE blcm_db;

-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    password_plain VARCHAR(255) NULL,
    full_name VARCHAR(100) NOT NULL,
    display_name VARCHAR(100) NULL,
    title VARCHAR(100) NULL,
    email VARCHAR(255) NULL,
    smtp_email VARCHAR(255) NULL,
    smtp_app_password VARCHAR(255) NULL,
    role ENUM('admin','manager','attorney','paralegal','billing','accounting') NOT NULL DEFAULT 'paralegal',
    commission_rate DECIMAL(5,2) DEFAULT 10.00,
    uses_presuit_offer TINYINT(1) DEFAULT 1,
    permissions TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. CASES (MR)
-- ============================================================
CREATE TABLE IF NOT EXISTS cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NULL,
    adjuster_3rd_id INT NULL,
    adjuster_um_id INT NULL,
    case_number VARCHAR(50) NOT NULL UNIQUE,
    client_name VARCHAR(100) NOT NULL,
    client_dob DATE NULL,
    doi DATE NULL,
    assigned_to INT NULL,
    status ENUM('ini','rec','verification','rfd','neg','lit','final_verification','accounting','closed') NOT NULL DEFAULT 'ini',
    treatment_status ENUM('in_treatment','treatment_done','neg','rfd') NULL,
    treatment_end_date DATE NULL,
    settlement_amount DECIMAL(12,2) DEFAULT 0,
    attorney_fee_percent DECIMAL(5,4) DEFAULT 0.3333,
    coverage_3rd_party TINYINT(1) DEFAULT 0,
    coverage_um TINYINT(1) DEFAULT 0,
    coverage_uim TINYINT(1) DEFAULT 0,
    policy_limit TINYINT(1) DEFAULT 0,
    um_uim_limit TINYINT(1) DEFAULT 0,
    pip_subrogation_amount DECIMAL(12,2) DEFAULT 0,
    pip_insurance_company VARCHAR(255) NULL,
    settlement_method VARCHAR(20) NULL,
    attorney_name VARCHAR(100) NULL,
    ini_completed TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (adjuster_3rd_id) REFERENCES adjusters(id) ON DELETE SET NULL,
    FOREIGN KEY (adjuster_um_id) REFERENCES adjusters(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_cases_client (client_id),
    INDEX idx_cases_adjuster_3rd (adjuster_3rd_id),
    INDEX idx_cases_adjuster_um (adjuster_um_id),
    INDEX idx_cases_status (status),
    INDEX idx_cases_treatment_status (treatment_status),
    INDEX idx_cases_assigned (assigned_to),
    INDEX idx_cases_case_number (case_number)
) ENGINE=InnoDB;

-- ============================================================
-- 2b. CLIENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    dob DATE NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    address_street VARCHAR(300) NULL,
    address_city VARCHAR(100) NULL,
    address_state VARCHAR(2) NULL,
    address_zip VARCHAR(10) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clients_name_dob (name, dob)
) ENGINE=InnoDB;

-- ============================================================
-- 3. PROVIDERS
-- ============================================================
CREATE TABLE IF NOT EXISTS providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    type ENUM('hospital','er','chiro','imaging','physician','surgery_center','pharmacy','acupuncture','massage','pain_management','pt','other') NOT NULL DEFAULT 'other',
    address VARCHAR(300) NULL,
    phone VARCHAR(20) NULL,
    fax VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    portal_url VARCHAR(300) NULL,
    preferred_method ENUM('email','fax','portal','phone','mail','chartswap','online') NOT NULL DEFAULT 'fax',
    uses_third_party TINYINT(1) NOT NULL DEFAULT 0,
    third_party_name VARCHAR(200) NULL,
    third_party_contact VARCHAR(200) NULL,
    avg_response_days INT NULL,
    difficulty_level ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_providers_name (name),
    INDEX idx_providers_type (type)
) ENGINE=InnoDB;

-- ============================================================
-- 4. PROVIDER CONTACTS
-- ============================================================
CREATE TABLE IF NOT EXISTS provider_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    department VARCHAR(100) NULL,
    contact_type ENUM('email','fax','portal','phone') NOT NULL,
    contact_value VARCHAR(200) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    verified_at DATE NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
    INDEX idx_provider_contacts_provider (provider_id)
) ENGINE=InnoDB;

-- ============================================================
-- 5. INSURANCE COMPANIES
-- ============================================================
CREATE TABLE IF NOT EXISTS insurance_companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('auto','health','workers_comp','liability','um_uim','other') NOT NULL DEFAULT 'auto',
    phone VARCHAR(50) NULL,
    fax VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    address VARCHAR(300) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(2) NULL,
    zip VARCHAR(10) NULL,
    website VARCHAR(300) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ins_co_name (name),
    INDEX idx_ins_co_type (type)
) ENGINE=InnoDB;

-- ============================================================
-- 6. ADJUSTERS
-- ============================================================
CREATE TABLE IF NOT EXISTS adjusters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insurance_company_id INT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    title VARCHAR(100) NULL,
    adjuster_type ENUM('pip','um','uim','3rd_party','liability','pd','bi') NULL,
    phone VARCHAR(50) NULL,
    fax VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    notes TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (insurance_company_id) REFERENCES insurance_companies(id) ON DELETE SET NULL,
    INDEX idx_adjusters_name (last_name, first_name),
    INDEX idx_adjusters_type (adjuster_type),
    INDEX idx_adjusters_ins_co (insurance_company_id)
) ENGINE=InnoDB;

-- ============================================================
-- 7. CASE PROVIDERS
-- ============================================================
CREATE TABLE IF NOT EXISTS case_providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    provider_id INT NOT NULL,
    treatment_start_date DATE NULL,
    treatment_end_date DATE NULL,
    record_types_needed SET('medical_records','billing','chart','imaging','op_report') NULL,
    overall_status ENUM('not_started','requesting','follow_up','action_needed','received_partial','on_hold','received_complete','verified') NOT NULL DEFAULT 'not_started',
    request_mr TINYINT(1) NOT NULL DEFAULT 0,
    request_bill TINYINT(1) NOT NULL DEFAULT 0,
    request_chart TINYINT(1) NOT NULL DEFAULT 0,
    request_img TINYINT(1) NOT NULL DEFAULT 0,
    request_op TINYINT(1) NOT NULL DEFAULT 0,
    received_date DATE NULL,
    assigned_to INT NULL,
    assignment_status ENUM('pending','accepted','declined') DEFAULT NULL,
    activated_by INT NULL,
    deadline DATE NULL,
    notes TEXT NULL,
    is_on_hold TINYINT(1) NOT NULL DEFAULT 0,
    hold_reason VARCHAR(255) NULL,
    no_records_reason VARCHAR(50) NULL,
    no_records_detail TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_case_providers_case (case_id),
    INDEX idx_case_providers_provider (provider_id),
    INDEX idx_case_providers_status (overall_status),
    INDEX idx_case_providers_hold (is_on_hold)
) ENGINE=InnoDB;

-- ============================================================
-- 8. LETTER TEMPLATES (must be before record_requests)
-- ============================================================
CREATE TABLE IF NOT EXISTS letter_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    template_type ENUM('medical_records','health_ledger','bulk_request','custom','balance_verification') NOT NULL DEFAULT 'custom',
    subject_template VARCHAR(255) NULL,
    body_template LONGTEXT NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (template_type),
    INDEX idx_default (is_default, template_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- 9. LETTER TEMPLATE VERSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS letter_template_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    version_number INT NOT NULL,
    body_template LONGTEXT NOT NULL,
    subject_template VARCHAR(255) NULL,
    changed_by INT NULL,
    change_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES letter_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_template (template_id),
    INDEX idx_version (template_id, version_number)
) ENGINE=InnoDB;

-- ============================================================
-- 10. RECORD REQUESTS
-- ============================================================
CREATE TABLE IF NOT EXISTS record_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_provider_id INT NOT NULL,
    template_id INT NULL,
    request_date DATE NOT NULL,
    request_method ENUM('email','fax','portal','phone','mail','chartswap','online') NOT NULL,
    request_type ENUM('initial','follow_up','re_request','rfd') NOT NULL DEFAULT 'initial',
    sent_to VARCHAR(200) NULL,
    department VARCHAR(100) NULL,
    authorization_sent TINYINT(1) NOT NULL DEFAULT 0,
    requested_by INT NULL,
    notes TEXT NULL,
    send_status ENUM('draft','sending','sent','failed') NOT NULL DEFAULT 'draft',
    sent_at DATETIME NULL,
    send_error TEXT NULL,
    send_attempts INT NOT NULL DEFAULT 0,
    letter_html LONGTEXT NULL,
    template_data JSON NULL,
    next_followup_date DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES letter_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_record_requests_cp (case_provider_id),
    INDEX idx_record_requests_followup (next_followup_date),
    INDEX idx_record_requests_send_status (send_status)
) ENGINE=InnoDB;

-- ============================================================
-- 11. RECORD RECEIPTS
-- ============================================================
CREATE TABLE IF NOT EXISTS record_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_provider_id INT NOT NULL,
    received_date DATE NOT NULL,
    received_method ENUM('email','fax','portal','mail','in_person') NOT NULL,
    has_medical_records TINYINT(1) NOT NULL DEFAULT 0,
    has_billing TINYINT(1) NOT NULL DEFAULT 0,
    has_chart TINYINT(1) NOT NULL DEFAULT 0,
    has_imaging TINYINT(1) NOT NULL DEFAULT 0,
    has_op_report TINYINT(1) NOT NULL DEFAULT 0,
    is_complete TINYINT(1) NOT NULL DEFAULT 0,
    incomplete_reason TEXT NULL,
    file_location VARCHAR(500) NULL,
    received_by INT NULL,
    verified_by INT NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_record_receipts_cp (case_provider_id)
) ENGINE=InnoDB;

-- ============================================================
-- 12. CASE NOTES
-- ============================================================
CREATE TABLE IF NOT EXISTS case_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    case_provider_id INT NULL,
    user_id INT NOT NULL,
    note_type ENUM('general','follow_up','issue','handoff') NOT NULL DEFAULT 'general',
    contact_method ENUM('phone','fax','email','portal','mail','in_person','other') NULL,
    contact_date DATETIME NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_case_notes_case (case_id)
) ENGINE=InnoDB;

-- ============================================================
-- 13. CASE DOCUMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS case_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    case_provider_id INT NULL,
    document_type ENUM('hipaa_authorization','signed_release','other') NOT NULL DEFAULT 'other',
    file_name VARCHAR(255) NOT NULL,
    original_file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by INT NULL,
    notes TEXT NULL,
    is_provider_template TINYINT(1) NOT NULL DEFAULT 0,
    provider_name_x DECIMAL(8,1) NULL,
    provider_name_y DECIMAL(8,1) NULL,
    provider_name_width DECIMAL(8,1) NULL,
    provider_name_height DECIMAL(8,1) NULL,
    provider_name_font_size INT NULL DEFAULT 12,
    use_date_overlay TINYINT(1) NOT NULL DEFAULT 0,
    date_x DECIMAL(8,1) NULL,
    date_y DECIMAL(8,1) NULL,
    date_width DECIMAL(8,1) NULL,
    date_height DECIMAL(8,1) NULL,
    date_font_size INT NULL DEFAULT 12,
    use_custom_text_overlay TINYINT(1) NOT NULL DEFAULT 0,
    custom_text_value TEXT NULL,
    custom_text_x DECIMAL(8,1) NULL,
    custom_text_y DECIMAL(8,1) NULL,
    custom_text_width DECIMAL(8,1) NULL,
    custom_text_height DECIMAL(8,1) NULL,
    custom_text_font_size INT NOT NULL DEFAULT 12,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_case_docs_case (case_id),
    INDEX idx_case_docs_provider (case_provider_id),
    INDEX idx_case_docs_type (document_type),
    INDEX idx_case_docs_template (is_provider_template)
) ENGINE=InnoDB;

-- ============================================================
-- 14. REQUEST ATTACHMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS request_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_request_id INT NOT NULL,
    case_document_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (record_request_id) REFERENCES record_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (case_document_id) REFERENCES case_documents(id) ON DELETE CASCADE,
    INDEX idx_request (record_request_id),
    INDEX idx_document (case_document_id),
    UNIQUE KEY unique_request_document (record_request_id, case_document_id)
) ENGINE=InnoDB;

-- ============================================================
-- 15. NOTIFICATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    case_provider_id INT NULL,
    type VARCHAR(50) NOT NULL,
    message VARCHAR(500) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    due_date DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE SET NULL,
    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_unread (user_id, is_read)
) ENGINE=InnoDB;

-- ============================================================
-- 16. ACTIVITY LOG
-- ============================================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NULL,
    details JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activity_log_entity (entity_type, entity_id),
    INDEX idx_activity_log_user (user_id)
) ENGINE=InnoDB;

-- ============================================================
-- 17. MESSAGES
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_messages_to_user (to_user_id),
    INDEX idx_messages_from_user (from_user_id),
    INDEX idx_messages_is_read (is_read)
) ENGINE=InnoDB;

-- ============================================================
-- 18. SEND LOG
-- ============================================================
CREATE TABLE IF NOT EXISTS send_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_request_id INT NOT NULL,
    send_method ENUM('email','fax') NOT NULL,
    recipient VARCHAR(200) NOT NULL,
    status ENUM('success','failed') NOT NULL,
    external_id VARCHAR(200) NULL,
    error_message TEXT NULL,
    sent_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (record_request_id) REFERENCES record_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_send_log_request (record_request_id)
) ENGINE=InnoDB;

-- ============================================================
-- 19. DEADLINE CHANGES
-- ============================================================
CREATE TABLE IF NOT EXISTS deadline_changes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_provider_id INT NOT NULL,
    old_deadline DATE NOT NULL,
    new_deadline DATE NOT NULL,
    reason VARCHAR(500) NOT NULL,
    changed_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_deadline_changes_cp (case_provider_id)
) ENGINE=InnoDB;

-- ============================================================
-- 20. HEALTH LEDGER ITEMS
-- ============================================================
CREATE TABLE IF NOT EXISTS health_ledger_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NULL,
    case_number VARCHAR(20) NULL,
    client_name VARCHAR(255) NOT NULL,
    insurance_carrier VARCHAR(255) NOT NULL,
    claim_number VARCHAR(50) NULL,
    member_id VARCHAR(50) NULL,
    carrier_contact_email VARCHAR(255) NULL,
    carrier_contact_fax VARCHAR(50) NULL,
    overall_status ENUM('not_started','requesting','follow_up','received','done') DEFAULT 'not_started',
    assigned_to INT NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_hli_case_id (case_id),
    INDEX idx_hli_status (overall_status),
    INDEX idx_hli_assigned_to (assigned_to)
) ENGINE=InnoDB;

-- ============================================================
-- 21. HL REQUESTS (Health Ledger Requests)
-- ============================================================
CREATE TABLE IF NOT EXISTS hl_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    template_id INT NULL,
    request_type ENUM('initial','follow_up','re_request') DEFAULT 'initial',
    request_date DATE NOT NULL,
    request_method ENUM('fax','email','portal','phone','mail') NOT NULL,
    sent_to VARCHAR(255) NULL,
    send_status ENUM('draft','sending','sent','failed') DEFAULT 'draft',
    sent_at DATETIME NULL,
    send_error TEXT NULL,
    send_attempts INT DEFAULT 0,
    letter_html LONGTEXT NULL,
    template_data JSON NULL,
    next_followup_date DATE NULL,
    notes TEXT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES health_ledger_items(id) ON DELETE CASCADE,
    FOREIGN KEY (template_id) REFERENCES letter_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_hlr_item_id (item_id),
    INDEX idx_hlr_send_status (send_status)
) ENGINE=InnoDB;

-- ============================================================
-- 22. HL REQUEST ATTACHMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS hl_request_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hl_request_id INT NOT NULL,
    case_document_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hl_request_id) REFERENCES hl_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (case_document_id) REFERENCES case_documents(id) ON DELETE CASCADE,
    INDEX idx_hl_request (hl_request_id),
    INDEX idx_document (case_document_id),
    UNIQUE KEY unique_hl_request_document (hl_request_id, case_document_id)
) ENGINE=InnoDB;

-- ============================================================
-- 23. MBR REPORTS
-- ============================================================
CREATE TABLE IF NOT EXISTS mbr_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL UNIQUE,
    pip1_name VARCHAR(255) NULL,
    pip2_name VARCHAR(255) NULL,
    health1_name VARCHAR(255) NULL,
    health2_name VARCHAR(255) NULL,
    health3_name VARCHAR(255) NULL,
    has_wage_loss TINYINT(1) DEFAULT 0,
    has_essential_service TINYINT(1) DEFAULT 0,
    has_health_subrogation TINYINT(1) DEFAULT 0,
    has_health_subrogation2 TINYINT(1) DEFAULT 0,
    status ENUM('draft','completed','approved') DEFAULT 'draft',
    completed_by INT NULL,
    completed_at DATETIME NULL,
    approved_by INT NULL,
    approved_at DATETIME NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_mbr_reports_case (case_id),
    INDEX idx_mbr_reports_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- 24. MBR LINES
-- ============================================================
CREATE TABLE IF NOT EXISTS mbr_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    line_type ENUM('provider','bridge_law','wage_loss','essential_service','health_subrogation','health_subrogation2','rx') NOT NULL,
    provider_name VARCHAR(255) NULL,
    case_provider_id INT NULL,
    charges DECIMAL(12,2) DEFAULT 0,
    pip1_amount DECIMAL(12,2) DEFAULT 0,
    pip2_amount DECIMAL(12,2) DEFAULT 0,
    health1_amount DECIMAL(12,2) DEFAULT 0,
    health2_amount DECIMAL(12,2) DEFAULT 0,
    health3_amount DECIMAL(12,2) DEFAULT 0,
    discount DECIMAL(12,2) DEFAULT 0,
    office_paid DECIMAL(12,2) DEFAULT 0,
    client_paid DECIMAL(12,2) DEFAULT 0,
    balance DECIMAL(12,2) DEFAULT 0,
    treatment_dates VARCHAR(100) NULL,
    visits VARCHAR(50) NULL,
    note TEXT NULL,
    record_types_needed SET('medical_records','billing','chart','imaging','op_report') NULL,
    ini_status ENUM('pending','complete') NOT NULL DEFAULT 'pending',
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES mbr_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE SET NULL,
    INDEX idx_mbr_lines_report (report_id)
) ENGINE=InnoDB;

-- ============================================================
-- 25. MR FEE PAYMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS mr_fee_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    case_provider_id INT NULL,
    expense_category ENUM('mr_cost','litigation','other') NOT NULL DEFAULT 'mr_cost',
    provider_name VARCHAR(200) NULL,
    description VARCHAR(255) NULL,
    billed_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_type ENUM('check','card','cash','wire','other') NULL,
    check_number VARCHAR(50) NULL,
    payment_date DATE NULL,
    paid_by INT NULL,
    receipt_document_id INT NULL,
    notes TEXT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE SET NULL,
    FOREIGN KEY (paid_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (receipt_document_id) REFERENCES case_documents(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_mr_fee_payments_case (case_id),
    INDEX idx_mr_fee_payments_cp (case_provider_id),
    INDEX idx_mr_fee_payments_date (payment_date),
    INDEX idx_mr_fee_payments_category (expense_category)
) ENGINE=InnoDB;

-- ============================================================
-- 26. BANK STATEMENT ENTRIES
-- ============================================================
CREATE TABLE IF NOT EXISTS bank_statement_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_id VARCHAR(36) NOT NULL,
    transaction_date DATE NOT NULL,
    description VARCHAR(500) NULL,
    amount DECIMAL(12,2) NOT NULL,
    check_number VARCHAR(50) NULL,
    reference_number VARCHAR(100) NULL,
    bank_category VARCHAR(100) NULL,
    reconciliation_status ENUM('unmatched','matched','ignored') NOT NULL DEFAULT 'unmatched',
    matched_payment_id INT NULL,
    matched_by INT NULL,
    matched_at DATETIME NULL,
    notes TEXT NULL,
    imported_by INT NOT NULL,
    imported_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (matched_payment_id) REFERENCES mr_fee_payments(id) ON DELETE SET NULL,
    FOREIGN KEY (matched_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (imported_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_bank_entries_batch (batch_id),
    INDEX idx_bank_entries_date (transaction_date),
    INDEX idx_bank_entries_check (check_number),
    INDEX idx_bank_entries_status (reconciliation_status),
    INDEX idx_bank_entries_matched (matched_payment_id)
) ENGINE=InnoDB;

-- ============================================================
-- 27. CASE NEGOTIATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS case_negotiations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    coverage_type ENUM('3rd_party','um','uim','dv') NOT NULL DEFAULT '3rd_party',
    insurance_company VARCHAR(255) NULL,
    round_number INT NOT NULL DEFAULT 1,
    demand_date DATE NULL,
    demand_amount DECIMAL(12,2) DEFAULT 0,
    offer_date DATE NULL,
    offer_amount DECIMAL(12,2) DEFAULT 0,
    party VARCHAR(255) NULL,
    adjuster_phone VARCHAR(50) NULL,
    adjuster_fax VARCHAR(50) NULL,
    adjuster_email VARCHAR(255) NULL,
    claim_number VARCHAR(100) NULL,
    status ENUM('pending','countered','accepted','rejected') DEFAULT 'pending',
    notes TEXT NULL,
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_case_neg_case (case_id),
    INDEX idx_case_neg_coverage (coverage_type)
) ENGINE=InnoDB;

-- ============================================================
-- 28. PROVIDER NEGOTIATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS provider_negotiations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    case_provider_id INT NULL,
    mbr_line_id INT NULL,
    provider_name VARCHAR(255) NOT NULL,
    original_balance DECIMAL(12,2) DEFAULT 0,
    requested_reduction DECIMAL(12,2) DEFAULT 0,
    accepted_amount DECIMAL(12,2) DEFAULT 0,
    reduction_percent DECIMAL(5,2) DEFAULT 0,
    status ENUM('pending','negotiating','accepted','rejected','waived') DEFAULT 'pending',
    contact_name VARCHAR(255) NULL,
    contact_info VARCHAR(255) NULL,
    notes TEXT NULL,
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (case_provider_id) REFERENCES case_providers(id) ON DELETE SET NULL,
    FOREIGN KEY (mbr_line_id) REFERENCES mbr_lines(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_prov_neg_case (case_id)
) ENGINE=InnoDB;

-- ============================================================
-- ============================================================
-- COMMISSION SYSTEM TABLES (13 new tables)
-- ============================================================
-- ============================================================

-- ============================================================
-- 29. ATTORNEY CASES (demand/litigation/UIM + attorney commission)
-- ============================================================
CREATE TABLE IF NOT EXISTS attorney_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NULL,
    case_number VARCHAR(50) NOT NULL,
    client_name VARCHAR(200) NOT NULL,
    case_type VARCHAR(50) DEFAULT 'Auto',
    attorney_user_id INT NOT NULL,
    created_by INT NOT NULL,

    phase ENUM('demand','litigation','uim','settled') DEFAULT 'demand',
    status VARCHAR(20) DEFAULT 'in_progress',
    stage VARCHAR(50) DEFAULT 'demand_review',

    -- Demand
    assigned_date DATE,
    demand_deadline DATE,
    demand_out_date DATE NULL,
    negotiate_date DATE NULL,
    demand_settled_date DATE NULL,
    demand_duration_days INT NULL,

    -- Top Offer
    top_offer_amount DECIMAL(15,2) NULL,
    top_offer_date DATE NULL,
    top_offer_assignee_id INT NULL,
    top_offer_note TEXT NULL,

    -- Litigation
    litigation_start_date DATE NULL,
    litigation_settled_date DATE NULL,
    litigation_duration_days INT NULL,
    presuit_offer DECIMAL(15,2) DEFAULT 0,
    resolution_type VARCHAR(100) NULL,
    fee_rate DECIMAL(5,2) NULL,

    -- UIM
    uim_start_date DATE NULL,
    uim_demand_out_date DATE NULL,
    uim_negotiate_date DATE NULL,
    uim_settled_date DATE NULL,
    uim_duration_days INT NULL,
    is_policy_limit TINYINT DEFAULT 0,

    -- Settlement
    settled DECIMAL(15,2) DEFAULT 0,
    difference DECIMAL(15,2) DEFAULT 0,
    legal_fee DECIMAL(15,2) DEFAULT 0,
    discounted_legal_fee DECIMAL(15,2) DEFAULT 0,
    uim_settled DECIMAL(15,2) DEFAULT 0,
    uim_legal_fee DECIMAL(15,2) DEFAULT 0,
    uim_discounted_legal_fee DECIMAL(15,2) DEFAULT 0,

    -- Commission (attorney)
    commission DECIMAL(15,2) DEFAULT 0,
    commission_type VARCHAR(50) NULL,
    uim_commission DECIMAL(15,2) DEFAULT 0,

    -- Billing Final Balance Checkup
    sent_to_billing_final_date DATE NULL,
    billing_final_assigned_to INT NULL,

    -- Accounting
    sent_to_accounting_date DATE NULL,
    accounting_assigned_to INT NULL,

    -- Meta
    month VARCHAR(20) NULL,
    note TEXT NULL,
    check_received TINYINT DEFAULT 0,
    is_marketing TINYINT DEFAULT 0,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_attorney (attorney_user_id),
    INDEX idx_phase (phase),
    INDEX idx_case_number (case_number),
    INDEX idx_month (month),
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB;

-- ============================================================
-- 29b. ATTORNEY CASE TRANSFERS
-- ============================================================
CREATE TABLE IF NOT EXISTS attorney_case_transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attorney_case_id INT NOT NULL,
    from_attorney_id INT NOT NULL,
    to_attorney_id INT NOT NULL,
    note TEXT NULL,
    transferred_by INT NOT NULL,
    transferred_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    from_start_date DATE NULL,
    FOREIGN KEY (attorney_case_id) REFERENCES attorney_cases(id),
    FOREIGN KEY (from_attorney_id) REFERENCES users(id),
    FOREIGN KEY (to_attorney_id) REFERENCES users(id),
    FOREIGN KEY (transferred_by) REFERENCES users(id),
    INDEX idx_transfer_case (attorney_case_id)
) ENGINE=InnoDB;

-- ============================================================
-- 30. EMPLOYEE COMMISSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS employee_commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_number VARCHAR(50) NOT NULL,
    client_name VARCHAR(200) NOT NULL,
    case_type VARCHAR(50) DEFAULT 'Auto',
    employee_user_id INT NOT NULL,
    created_by INT NOT NULL,

    settled DECIMAL(15,2) DEFAULT 0,
    presuit_offer DECIMAL(15,2) DEFAULT 0,
    difference DECIMAL(15,2) DEFAULT 0,
    fee_rate DECIMAL(5,2) DEFAULT 33.33,
    legal_fee DECIMAL(15,2) DEFAULT 0,
    discounted_legal_fee DECIMAL(15,2) DEFAULT 0,

    commission_rate DECIMAL(5,2) NOT NULL,
    commission DECIMAL(15,2) DEFAULT 0,
    is_marketing TINYINT DEFAULT 0,

    status ENUM('in_progress','unpaid','paid','rejected') DEFAULT 'unpaid',
    check_received TINYINT DEFAULT 0,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,

    month VARCHAR(20) NULL,
    note TEXT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_employee (employee_user_id),
    INDEX idx_status (status),
    INDEX idx_month (month),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB;

-- ============================================================
-- 31. REFERRAL ENTRIES
-- ============================================================
CREATE TABLE IF NOT EXISTS referral_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    row_number INT,
    signed_date DATE,
    file_number VARCHAR(50),
    client_name VARCHAR(300),
    status VARCHAR(20),
    date_of_loss DATE,
    referred_by VARCHAR(200),
    referred_to_provider VARCHAR(200),
    referred_to_body_shop VARCHAR(200),
    referral_type VARCHAR(100),
    lead_id INT,
    case_manager_id INT,
    remark TEXT,
    entry_month VARCHAR(20),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_entry_month (entry_month),
    INDEX idx_file_number (file_number),
    INDEX idx_deleted (deleted_at)
) ENGINE=InnoDB;

-- ============================================================
-- 32. TRAFFIC CASES
-- ============================================================
CREATE TABLE IF NOT EXISTS traffic_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    client_name VARCHAR(200),
    client_phone VARCHAR(50),
    client_email VARCHAR(200),
    court VARCHAR(100),
    court_date DATETIME,
    charge VARCHAR(200),
    case_number VARCHAR(50),
    prosecutor_offer TEXT,
    disposition ENUM('pending','dismissed','amended','other'),
    commission DECIMAL(10,2) DEFAULT 0,
    discovery TINYINT DEFAULT 0,
    status ENUM('active','resolved') DEFAULT 'active',
    note TEXT,
    referral_source VARCHAR(100),
    paid TINYINT DEFAULT 0,
    paid_at DATETIME,
    noa_sent_date DATE,
    citation_issued_date DATE,
    request_id INT,
    requested_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,

    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_case_number (case_number)
) ENGINE=InnoDB;

-- ============================================================
-- 33. TRAFFIC CASE FILES
-- ============================================================
CREATE TABLE IF NOT EXISTS traffic_case_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    filename VARCHAR(255),
    original_name VARCHAR(255),
    file_type VARCHAR(100),
    file_size INT,
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES traffic_cases(id) ON DELETE CASCADE,
    INDEX idx_case (case_id)
) ENGINE=InnoDB;

-- ============================================================
-- 34. DEMAND REQUESTS (workflow)
-- ============================================================
CREATE TABLE IF NOT EXISTS demand_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requested_by INT,
    assigned_to INT,
    case_number VARCHAR(50),
    client_name VARCHAR(200),
    case_type VARCHAR(50) DEFAULT 'Auto',
    note TEXT,
    status ENUM('pending','accepted','denied') DEFAULT 'pending',
    deny_reason TEXT,
    responded_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_assigned (assigned_to)
) ENGINE=InnoDB;

-- ============================================================
-- 35. TRAFFIC REQUESTS (workflow)
-- ============================================================
CREATE TABLE IF NOT EXISTS traffic_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requested_by INT,
    assigned_to INT,
    client_name VARCHAR(200),
    client_phone VARCHAR(50),
    client_email VARCHAR(200),
    court VARCHAR(100),
    court_date DATETIME,
    charge VARCHAR(200),
    case_number VARCHAR(50),
    note TEXT,
    referral_source VARCHAR(100),
    status ENUM('pending','accepted','denied') DEFAULT 'pending',
    deny_reason TEXT,
    citation_issued_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at DATETIME,

    INDEX idx_status (status),
    INDEX idx_assigned (assigned_to)
) ENGINE=InnoDB;

-- ============================================================
-- 36. DEADLINE EXTENSION REQUESTS
-- ============================================================
CREATE TABLE IF NOT EXISTS deadline_extension_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT,
    user_id INT,
    current_deadline DATE,
    requested_deadline DATE,
    reason TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_note TEXT,
    reviewed_by INT,
    reviewed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================================
-- 37. EMPLOYEE GOALS
-- ============================================================
CREATE TABLE IF NOT EXISTS employee_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    year INT,
    target_cases INT DEFAULT 50,
    target_legal_fee DECIMAL(15,2) DEFAULT 500000.00,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_year (user_id, year)
) ENGINE=InnoDB;

-- ============================================================
-- 38. PERFORMANCE SNAPSHOTS
-- ============================================================
CREATE TABLE IF NOT EXISTS performance_snapshots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    snapshot_month VARCHAR(7),
    cases_settled INT DEFAULT 0,
    demand_settled INT DEFAULT 0,
    litigation_settled INT DEFAULT 0,
    total_commission DECIMAL(15,2) DEFAULT 0,
    new_cases_received INT DEFAULT 0,
    avg_demand_days DECIMAL(5,1),
    avg_litigation_days DECIMAL(5,1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_employee_month (employee_id, snapshot_month)
) ENGINE=InnoDB;

-- ============================================================
-- 39. MANAGER TEAM
-- ============================================================
CREATE TABLE IF NOT EXISTS manager_team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    manager_id INT,
    employee_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_manager_employee (manager_id, employee_id)
) ENGINE=InnoDB;

-- ============================================================
-- 40. LITIGATION CASES (legacy tracking)
-- ============================================================
CREATE TABLE IF NOT EXISTS litigation_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    case_number VARCHAR(50),
    client_name VARCHAR(200),
    injury_type VARCHAR(100),
    opposing_insurance VARCHAR(255),
    litigation_stage ENUM('filed','post','after_dep','mediation','post_arb','arb','settle'),
    next_deadline DATE,
    deadline_description VARCHAR(255),
    settlement_amount DECIMAL(15,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_user (user_id),
    INDEX idx_case_number (case_number)
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA: Default admin user (password: admin123)
-- ============================================================
INSERT INTO users (username, password_hash, full_name, display_name, role, commission_rate, permissions, is_active)
VALUES (
    'admin',
    '$2y$10$KSL7LYw6ATdIi0BmOGBL..PgIixIOkCQz5iQLL9FT/Vyn7PKL9TYW',
    'System Admin',
    'Admin',
    'admin',
    10.00,
    NULL,
    1
);

-- ============================================================
-- DONE — 40 tables total (28 MRMS + 12 Commission)
-- ============================================================
