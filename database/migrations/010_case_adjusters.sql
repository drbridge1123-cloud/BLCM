-- Create case_adjusters junction table
CREATE TABLE IF NOT EXISTS case_adjusters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    adjuster_id INT NOT NULL,
    coverage_type ENUM('3rd_party','um','uim','pip','liability','pd','bi') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    FOREIGN KEY (adjuster_id) REFERENCES adjusters(id) ON DELETE CASCADE,
    UNIQUE KEY uk_case_coverage (case_id, coverage_type),
    INDEX idx_ca_case (case_id)
) ENGINE=InnoDB;

-- Migrate existing data from cases.adjuster_3rd_id and cases.adjuster_um_id
INSERT IGNORE INTO case_adjusters (case_id, adjuster_id, coverage_type)
SELECT id, adjuster_3rd_id, '3rd_party' FROM cases WHERE adjuster_3rd_id IS NOT NULL;

INSERT IGNORE INTO case_adjusters (case_id, adjuster_id, coverage_type)
SELECT id, adjuster_um_id, 'um' FROM cases WHERE adjuster_um_id IS NOT NULL;
