-- Migration 004: Team-Based Trackers
-- Adds team field, prelitigation status, workflow dates, and new tracker tables

-- 1. Add team to users
ALTER TABLE users ADD COLUMN team VARCHAR(50) NULL AFTER role;
CREATE INDEX idx_users_team ON users(team);

-- 2. Add prelitigation status to cases
ALTER TABLE cases MODIFY COLUMN status ENUM(
  'prelitigation','collecting','verification','completed','rfd',
  'final_verification','disbursement','accounting','closed'
) NOT NULL DEFAULT 'prelitigation';

-- 3. Add client contact fields
ALTER TABLE cases
  ADD COLUMN client_phone VARCHAR(20) NULL AFTER client_dob,
  ADD COLUMN client_email VARCHAR(255) NULL AFTER client_phone;

-- 4. Add workflow date tracking columns
ALTER TABLE cases
  ADD COLUMN prelitigation_start_date DATE NULL,
  ADD COLUMN sent_to_billing_date DATE NULL,
  ADD COLUMN sent_to_attorney_date DATE NULL,
  ADD COLUMN sent_to_billing_final_date DATE NULL,
  ADD COLUMN sent_to_accounting_date DATE NULL,
  ADD COLUMN closed_date DATE NULL,
  ADD COLUMN file_location VARCHAR(500) NULL;

-- 5. Prelitigation follow-up log table
CREATE TABLE IF NOT EXISTS prelitigation_followups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NOT NULL,
  followup_date DATE NOT NULL,
  followup_type ENUM('phone','email','text','in_person','other') NOT NULL DEFAULT 'phone',
  contact_result ENUM('reached','voicemail','no_answer','callback_scheduled','treatment_update') NOT NULL DEFAULT 'reached',
  treatment_status_update VARCHAR(255) NULL,
  next_followup_date DATE NULL,
  notes TEXT NULL,
  created_by INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_prelit_case (case_id),
  INDEX idx_prelit_next (next_followup_date)
) ENGINE=InnoDB;

-- 6. Accounting disbursement line items table
CREATE TABLE IF NOT EXISTS accounting_disbursements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  case_id INT NULL,
  attorney_case_id INT NULL,
  disbursement_type ENUM('client_payment','provider_payment','attorney_fee','mr_cost_reimbursement','lien_payment','other') NOT NULL,
  payee_name VARCHAR(200) NOT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  check_number VARCHAR(50) NULL,
  payment_method ENUM('check','wire','ach','cash','other') NULL,
  payment_date DATE NULL,
  status ENUM('pending','issued','cleared','void') NOT NULL DEFAULT 'pending',
  notes TEXT NULL,
  created_by INT NULL,
  processed_by INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_acct_case (case_id),
  INDEX idx_acct_status (status)
) ENGINE=InnoDB;
