-- Migration 005: Rename case status codes
-- Old → New mapping:
--   prelitigation      → ini
--   collecting         → ini
--   verification       → rec
--   completed          → verification
--   rfd                → rfd (no change)
--   final_verification → neg
--   disbursement       → final_verification
--   accounting         → accounting (no change)
--   closed             → closed (no change)

-- Step 1: Expand ENUM to include both old and new values
ALTER TABLE cases MODIFY COLUMN status
    ENUM('prelitigation','collecting','verification','completed','rfd','final_verification','disbursement','accounting','closed','ini','rec','neg')
    NOT NULL DEFAULT 'ini';

-- Step 2: Migrate data (order matters to avoid conflicts)
UPDATE cases SET status = 'ini' WHERE status IN ('collecting', 'prelitigation');
UPDATE cases SET status = 'rec' WHERE status = 'verification';
UPDATE cases SET status = 'neg' WHERE status = 'final_verification';
UPDATE cases SET status = 'verification' WHERE status = 'completed';
UPDATE cases SET status = 'final_verification' WHERE status = 'disbursement';

-- Step 3: Shrink ENUM to only new values
ALTER TABLE cases MODIFY COLUMN status
    ENUM('ini','rec','verification','rfd','neg','final_verification','accounting','closed')
    NOT NULL DEFAULT 'ini';
