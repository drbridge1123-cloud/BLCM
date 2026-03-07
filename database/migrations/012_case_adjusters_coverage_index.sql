-- Add coverage_index to case_adjusters for multiple instances of same coverage type
-- e.g., two 3rd Party adjusters for multi-party accidents
ALTER TABLE case_adjusters ADD COLUMN coverage_index INT NOT NULL DEFAULT 1 AFTER coverage_type;

-- Drop old unique key and add new one with coverage_index
ALTER TABLE case_adjusters DROP INDEX uk_case_coverage, ADD UNIQUE KEY uk_case_coverage_idx (case_id, coverage_type, coverage_index);
