-- Add coverage_index to support multiple instances of same coverage type
-- e.g., two 3rd Party negotiations for multi-party accidents
ALTER TABLE case_negotiations ADD COLUMN coverage_index INT NOT NULL DEFAULT 1 AFTER coverage_type;
