-- Migration 006: Add adjuster FK columns to cases for Contacts modal
ALTER TABLE cases
  ADD COLUMN adjuster_3rd_id INT NULL AFTER client_id,
  ADD COLUMN adjuster_um_id INT NULL AFTER adjuster_3rd_id;

ALTER TABLE cases
  ADD CONSTRAINT fk_cases_adjuster_3rd FOREIGN KEY (adjuster_3rd_id) REFERENCES adjusters(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_cases_adjuster_um FOREIGN KEY (adjuster_um_id) REFERENCES adjusters(id) ON DELETE SET NULL;

CREATE INDEX idx_cases_adjuster_3rd ON cases(adjuster_3rd_id);
CREATE INDEX idx_cases_adjuster_um ON cases(adjuster_um_id);
