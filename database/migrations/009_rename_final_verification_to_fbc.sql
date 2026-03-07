-- Migration 009: Rename status 'final_verification' to 'fbc' (Final Balance Check)
-- Run: mysql -u root blcm_db < database/migrations/009_rename_final_verification_to_fbc.sql

-- Step 1: Modify the ENUM to include 'fbc' alongside 'final_verification'
ALTER TABLE cases MODIFY COLUMN status
    ENUM('ini','rec','verification','rfd','neg','lit','final_verification','fbc','accounting','closed')
    NOT NULL DEFAULT 'ini';

-- Step 2: Update existing rows
UPDATE cases SET status = 'fbc' WHERE status = 'final_verification';

-- Step 3: Remove 'final_verification' from ENUM
ALTER TABLE cases MODIFY COLUMN status
    ENUM('ini','rec','verification','rfd','neg','lit','fbc','accounting','closed')
    NOT NULL DEFAULT 'ini';
