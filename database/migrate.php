<?php
/**
 * CMC Data Migration Script
 * Migrates data from MRMS (mrms) + Commission (commission_db) → CMC (cmc_db)
 *
 * Run in browser: http://localhost/CMCdemo/database/migrate.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(600);

// Disable output buffering for real-time display
while (ob_get_level()) ob_end_flush();
ob_implicit_flush(true);
header('Content-Type: text/html; charset=utf-8');
header('X-Accel-Buffering: no');
header('Cache-Control: no-cache');

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>CMC Migration</title>';
echo '<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;font-size:13px;line-height:1.6}';
echo '.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}.info{color:#60a5fa}.head{color:#c084fc;font-weight:bold;font-size:15px}';
echo '.phase{background:#2a2a4a;padding:10px 15px;margin:15px 0 5px;border-left:3px solid #818cf8;font-size:14px;font-weight:bold;color:#a5b4fc}';
echo '.summary{background:#1e3a2e;padding:15px;margin:20px 0;border:1px solid #4ade80;border-radius:6px}';
echo '.fail-summary{background:#3a1e1e;border-color:#f87171}';
echo '</style></head><body>';
echo '<div class="head">═══════════════════════════════════════════</div>';
echo '<div class="head">  CMC Data Migration: MRMS + Commission → CMC</div>';
echo '<div class="head">═══════════════════════════════════════════</div><br>';
flush();

// ────────────────────────────────────────
// Database connections
// ────────────────────────────────────────
function connectDB($dbName) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname={$dbName};charset=utf8mb4", 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        msg("Failed to connect to {$dbName}: " . $e->getMessage(), 'err');
        return null;
    }
}

function msg($text, $class = '') {
    $cls = $class ? " class=\"{$class}\"" : '';
    echo "<div{$cls}>{$text}</div>";
    flush();
}

function phase($title) {
    echo "<div class=\"phase\">{$title}</div>";
    flush();
}

// ────────────────────────────────────────
// Connect to all 3 databases
// ────────────────────────────────────────
$mrms = connectDB('mrms');
$comm = connectDB('commission_db');
$cmc  = connectDB('cmc_db');

if (!$mrms || !$comm || !$cmc) {
    msg('Cannot proceed without all 3 database connections.', 'err');
    exit;
}
msg('Connected to all 3 databases: mrms, commission_db, cmc_db', 'ok');

// ────────────────────────────────────────
// Safety check & auto-truncate: clear existing data
// ────────────────────────────────────────
$allCmcTables = [
    // Reverse FK dependency order for safe truncation
    'hl_request_attachments', 'hl_requests', 'health_ledger_items',
    'bank_statement_entries', 'send_log', 'mbr_lines',
    'request_attachments', 'mr_fee_payments',
    'provider_negotiations', 'case_negotiations',
    'record_receipts', 'record_requests', 'deadline_changes',
    'case_notes', 'case_documents',
    'mbr_reports',
    'notifications', 'activity_log', 'messages',
    'case_providers',
    'letter_template_versions', 'letter_templates',
    'provider_contacts', 'adjusters',
    'cases', 'providers', 'insurance_companies',
    // Commission tables
    'manager_team', 'performance_snapshots', 'employee_goals',
    'deadline_extension_requests', 'traffic_requests', 'demand_requests',
    'traffic_case_files', 'traffic_cases',
    'referral_entries', 'litigation_cases',
    'employee_commissions', 'attorney_cases',
    // Tracker tables
    'prelitigation_followups', 'accounting_disbursements',
    'users',
];

$cmc->exec('SET FOREIGN_KEY_CHECKS = 0');
$hasData = false;
foreach ($allCmcTables as $t) {
    try {
        $count = (int)$cmc->query("SELECT COUNT(*) FROM {$t}")->fetchColumn();
        if ($count > 0) {
            $cmc->exec("TRUNCATE TABLE {$t}");
            msg("  Truncated {$t} ({$count} rows)", 'warn');
            $hasData = true;
        }
    } catch (PDOException $e) {
        // Table might not exist yet
    }
}
$cmc->exec('SET FOREIGN_KEY_CHECKS = 1');
if ($hasData) {
    msg('Existing data cleared from cmc_db', 'warn');
} else {
    msg('cmc_db tables are empty — ready for migration', 'ok');
}
echo '<br>';

// ────────────────────────────────────────
// Disable FK checks
// ────────────────────────────────────────
$cmc->exec('SET FOREIGN_KEY_CHECKS = 0');
msg('Foreign key checks disabled', 'info');

// ════════════════════════════════════════
// PHASE 0: Schema alignment — add columns that exist in MRMS but not CMC
// ════════════════════════════════════════
phase('Phase 0: Schema Alignment');

$alterations = [
    // providers: city, state, zip, charges_record_fee, is_suspicious
    "ALTER TABLE providers ADD COLUMN city VARCHAR(100) NULL AFTER address",
    "ALTER TABLE providers ADD COLUMN state VARCHAR(2) NULL AFTER city",
    "ALTER TABLE providers ADD COLUMN zip VARCHAR(10) NULL AFTER state",
    "ALTER TABLE providers ADD COLUMN charges_record_fee TINYINT(1) NOT NULL DEFAULT 0 AFTER difficulty_level",
    "ALTER TABLE providers ADD COLUMN is_suspicious TINYINT(1) NOT NULL DEFAULT 0 AFTER notes",

    // letter_templates: sort_order, expand template_type ENUM
    "ALTER TABLE letter_templates ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER is_active",
    "ALTER TABLE letter_templates MODIFY COLUMN template_type ENUM('medical_records','health_ledger','bulk_request','custom','balance_verification','discount_request') NOT NULL DEFAULT 'custom'",

    // mr_fee_payments: paid_date, split_group_id, split_total, split_count
    "ALTER TABLE mr_fee_payments ADD COLUMN paid_date DATE NULL AFTER payment_date",
    "ALTER TABLE mr_fee_payments ADD COLUMN split_group_id VARCHAR(36) NULL AFTER notes",
    "ALTER TABLE mr_fee_payments ADD COLUMN split_total DECIMAL(12,2) NULL AFTER split_group_id",
    "ALTER TABLE mr_fee_payments ADD COLUMN split_count INT NULL AFTER split_total",

    // bank_statement_entries: card_holder
    "ALTER TABLE bank_statement_entries ADD COLUMN card_holder VARCHAR(50) NULL AFTER check_number",
];

foreach ($alterations as $sql) {
    try {
        $cmc->exec($sql);
    } catch (PDOException $e) {
        // Column/modification may already exist — skip silently
        if (strpos($e->getMessage(), 'Duplicate column') === false) {
            msg('  ' . $e->getMessage(), 'warn');
        }
    }
}
msg('Schema alignment complete — extra columns added to providers, letter_templates, mr_fee_payments, bank_statement_entries', 'ok');

$stats = [];

/**
 * Copy an entire table from source DB to cmc_db, preserving IDs
 */
function copyTable($src, $cmc, $tableName, $srcTable = null, $transform = null, $ignoreDupes = false) {
    global $stats;
    $srcTable = $srcTable ?: $tableName;

    try {
        // Check if source table exists
        $check = $src->query("SHOW TABLES LIKE '{$srcTable}'");
        if ($check->rowCount() === 0) {
            msg("  {$tableName}: source table '{$srcTable}' does not exist — SKIPPED", 'warn');
            $stats[$tableName] = ['status' => 'skipped', 'reason' => 'not in source'];
            return 0;
        }

        $rows = $src->query("SELECT * FROM {$srcTable}")->fetchAll();
        $count = count($rows);
        if ($count === 0) {
            msg("  {$tableName}: 0 rows in source — SKIPPED", 'warn');
            $stats[$tableName] = ['status' => 'empty', 'count' => 0];
            return 0;
        }

        $inserted = 0;
        $errors = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            // Apply transform if provided
            if ($transform) {
                $row = $transform($row);
                if ($row === null) continue; // skip row
            }

            $cols = array_keys($row);
            $placeholders = array_fill(0, count($cols), '?');
            $colStr = implode(', ', array_map(fn($c) => "`{$c}`", $cols));
            $phStr = implode(', ', $placeholders);

            try {
                $verb = $ignoreDupes ? 'INSERT IGNORE' : 'INSERT';
                $stmt = $cmc->prepare("{$verb} INTO `{$tableName}` ({$colStr}) VALUES ({$phStr})");
                $stmt->execute(array_values($row));
                if ($stmt->rowCount() > 0) $inserted++;
                else $skipped++;
            } catch (PDOException $e) {
                $errors++;
                if ($errors <= 3) {
                    msg("    Error inserting into {$tableName} (id={$row['id']}): " . $e->getMessage(), 'err');
                }
            }
        }

        $status = $errors > 0 ? 'warn' : 'ok';
        $errMsg = $errors > 0 ? " ({$errors} errors)" : '';
        $skipMsg = $skipped > 0 ? " ({$skipped} dupes skipped)" : '';
        msg("  {$tableName}: {$inserted}/{$count} rows inserted{$errMsg}{$skipMsg}", $status);
        $stats[$tableName] = ['status' => $errors > 0 ? 'partial' : 'ok', 'count' => $inserted, 'total' => $count, 'errors' => $errors];
        return $inserted;
    } catch (PDOException $e) {
        msg("  {$tableName}: FAILED — " . $e->getMessage(), 'err');
        $stats[$tableName] = ['status' => 'failed', 'error' => $e->getMessage()];
        return 0;
    }
}

// ════════════════════════════════════════
// PHASE 1: MRMS Independent Tables
// ════════════════════════════════════════
phase('Phase 1: MRMS Independent Tables');

// Users — MRMS uses password_hash, CMC also uses password_hash
copyTable($mrms, $cmc, 'users');
copyTable($mrms, $cmc, 'providers');
copyTable($mrms, $cmc, 'insurance_companies');

// ════════════════════════════════════════
// PHASE 2: MRMS 1st-level Dependent Tables
// ════════════════════════════════════════
phase('Phase 2: MRMS 1st-level Dependent Tables');

copyTable($mrms, $cmc, 'cases', null, null, true); // ignoreDupes for duplicate case_numbers
copyTable($mrms, $cmc, 'adjusters');
copyTable($mrms, $cmc, 'provider_contacts');
copyTable($mrms, $cmc, 'letter_templates');
copyTable($mrms, $cmc, 'letter_template_versions');

// ════════════════════════════════════════
// PHASE 3: MRMS 2nd-level Dependent Tables
// ════════════════════════════════════════
phase('Phase 3: MRMS 2nd-level Dependent Tables');

// case_providers needs ENUM transform: treating→not_started, no_records→received_complete
copyTable($mrms, $cmc, 'case_providers', null, function($row) {
    if ($row['overall_status'] === 'treating') {
        $row['overall_status'] = 'not_started';
    } elseif ($row['overall_status'] === 'no_records') {
        $row['overall_status'] = 'received_complete';
    }
    return $row;
});

copyTable($mrms, $cmc, 'case_documents');
copyTable($mrms, $cmc, 'notifications');
copyTable($mrms, $cmc, 'activity_log');
copyTable($mrms, $cmc, 'messages');

// ════════════════════════════════════════
// PHASE 4: MRMS 3rd-level Dependent Tables
// ════════════════════════════════════════
phase('Phase 4: MRMS 3rd-level Dependent Tables');

// record_requests — MRMS doesn't have template_id, department, template_data
// We need to only copy columns that exist in MRMS and let CMC-only cols default to NULL
copyTable($mrms, $cmc, 'record_requests', null, function($row) {
    // Add CMC-only nullable columns if not present
    if (!isset($row['template_id'])) $row['template_id'] = null;
    if (!isset($row['department'])) $row['department'] = null;
    if (!isset($row['template_data'])) $row['template_data'] = null;
    return $row;
});

copyTable($mrms, $cmc, 'record_receipts');
copyTable($mrms, $cmc, 'case_notes');
copyTable($mrms, $cmc, 'request_attachments');
copyTable($mrms, $cmc, 'deadline_changes');
copyTable($mrms, $cmc, 'mbr_reports');
copyTable($mrms, $cmc, 'case_negotiations');
copyTable($mrms, $cmc, 'provider_negotiations');
copyTable($mrms, $cmc, 'mr_fee_payments');

// ════════════════════════════════════════
// PHASE 5: MRMS 4th-level Dependent Tables
// ════════════════════════════════════════
phase('Phase 5: MRMS 4th-level Dependent Tables');

copyTable($mrms, $cmc, 'mbr_lines');
copyTable($mrms, $cmc, 'send_log');
copyTable($mrms, $cmc, 'bank_statement_entries');
copyTable($mrms, $cmc, 'health_ledger_items');
copyTable($mrms, $cmc, 'hl_requests');
copyTable($mrms, $cmc, 'hl_request_attachments');

// ════════════════════════════════════════
// PHASE 6: Commission Data
// ════════════════════════════════════════
phase('Phase 6: Commission Data');

// Step 1: Merge Commission users into CMC users
msg('  Merging Commission users...', 'info');
$commUsers = $comm->query("SELECT * FROM users")->fetchAll();
$userIdMap = []; // commission user_id → cmc user_id

foreach ($commUsers as $cu) {
    // Check if username already exists in CMC (from MRMS import)
    $existing = $cmc->query("SELECT id FROM users WHERE username = " . $cmc->quote($cu['username']))->fetch();

    if ($existing) {
        // User exists — update commission fields
        $userIdMap[$cu['id']] = (int)$existing['id'];
        $cmc->prepare("UPDATE users SET commission_rate = ?, uses_presuit_offer = ?, display_name = COALESCE(display_name, ?) WHERE id = ?")
            ->execute([$cu['commission_rate'], $cu['uses_presuit_offer'], $cu['display_name'], $existing['id']]);
        msg("    User '{$cu['username']}' matched to CMC id={$existing['id']} — commission fields updated", 'info');
    } else {
        // New user — insert with mapped fields
        $role = 'staff';
        if (isset($cu['is_attorney']) && $cu['is_attorney']) $role = 'attorney';
        elseif ($cu['role'] === 'admin') $role = 'admin';
        elseif (isset($cu['is_manager']) && $cu['is_manager']) $role = 'manager';

        $stmt = $cmc->prepare("INSERT INTO users (username, password_hash, full_name, display_name, role, commission_rate, uses_presuit_offer, is_active, created_at)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $cu['username'],
            $cu['password'],  // Commission uses 'password', CMC uses 'password_hash'
            $cu['display_name'],
            $cu['display_name'],
            $role,
            $cu['commission_rate'],
            $cu['uses_presuit_offer'],
            $cu['is_active'],
            $cu['created_at'],
        ]);
        $newId = (int)$cmc->lastInsertId();
        $userIdMap[$cu['id']] = $newId;
        msg("    User '{$cu['username']}' created in CMC as id={$newId} (role={$role})", 'ok');
    }
}
$stats['commission_users'] = ['status' => 'ok', 'count' => count($commUsers), 'mapped' => count($userIdMap)];

// Step 2: Identify attorney user IDs in Commission DB
$attorneyUserIds = [];
$commAttorneys = $comm->query("SELECT id FROM users WHERE is_attorney = 1")->fetchAll();
foreach ($commAttorneys as $a) {
    $attorneyUserIds[] = (int)$a['id'];
}
msg('  Attorney user IDs in Commission: [' . implode(', ', $attorneyUserIds) . ']', 'info');

// Step 3: Commission cases → employee_commissions (non-attorney)
msg('  Migrating employee commissions...', 'info');
$empCases = $comm->query("SELECT c.*, u.commission_rate AS user_commission_rate FROM cases c JOIN users u ON c.user_id = u.id WHERE c.user_id NOT IN (" . (empty($attorneyUserIds) ? '0' : implode(',', $attorneyUserIds)) . ")")->fetchAll();
$empInserted = 0;
$empErrors = 0;

foreach ($empCases as $ec) {
    $cmcUserId = $userIdMap[$ec['user_id']] ?? $ec['user_id'];
    $cmcReviewedBy = ($ec['reviewed_by'] && isset($userIdMap[$ec['reviewed_by']])) ? $userIdMap[$ec['reviewed_by']] : $ec['reviewed_by'];

    // Status mapping: pending → unpaid
    $status = $ec['status'];
    if ($status === 'pending') $status = 'unpaid';

    try {
        $stmt = $cmc->prepare("INSERT INTO employee_commissions
            (case_number, client_name, case_type, employee_user_id, created_by,
             settled, presuit_offer, difference, fee_rate, legal_fee, discounted_legal_fee,
             commission_rate, commission, is_marketing,
             status, check_received, reviewed_at, reviewed_by,
             month, note, submitted_at, deleted_at)
            VALUES (?,?,?,?,?, ?,?,?,?,?,?, ?,?,?, ?,?,?,?, ?,?,?,?)");
        $stmt->execute([
            $ec['case_number'], $ec['client_name'], $ec['case_type'] ?? 'Auto',
            $cmcUserId, $cmcUserId,
            $ec['settled'], $ec['presuit_offer'], $ec['difference'],
            $ec['fee_rate'], $ec['legal_fee'], $ec['discounted_legal_fee'],
            $ec['user_commission_rate'] ?? 10.00, $ec['commission'], 0,
            $status, $ec['check_received'], $ec['reviewed_at'], $cmcReviewedBy,
            $ec['month'], $ec['note'], $ec['submitted_at'], $ec['deleted_at'],
        ]);
        $empInserted++;
    } catch (PDOException $e) {
        $empErrors++;
        if ($empErrors <= 3) msg("    Error: " . $e->getMessage(), 'err');
    }
}
$errStr = $empErrors > 0 ? " ({$empErrors} errors)" : '';
msg("  employee_commissions: {$empInserted}/" . count($empCases) . " inserted{$errStr}", $empErrors > 0 ? 'warn' : 'ok');
$stats['employee_commissions'] = ['status' => $empErrors > 0 ? 'partial' : 'ok', 'count' => $empInserted, 'total' => count($empCases), 'errors' => $empErrors];

// Step 4: Commission cases → attorney_cases (attorney users)
msg('  Migrating attorney cases...', 'info');
$attCases = $comm->query("SELECT * FROM cases WHERE user_id IN (" . (empty($attorneyUserIds) ? '0' : implode(',', $attorneyUserIds)) . ")")->fetchAll();
$attInserted = 0;
$attErrors = 0;

foreach ($attCases as $ac) {
    $cmcUserId = $userIdMap[$ac['user_id']] ?? $ac['user_id'];

    // Map phase/status
    $phase = $ac['phase'] ?? 'demand';
    if ($ac['status'] === 'paid' && empty($ac['phase'])) $phase = 'settled';
    $status = $ac['status'] === 'pending' ? 'in_progress' : $ac['status'];

    // Resolution type / fee_rate
    $resolutionType = $ac['resolution_type'] ?? null;
    $feeRate = $ac['fee_rate'] ?? null;

    try {
        $stmt = $cmc->prepare("INSERT INTO attorney_cases
            (case_number, client_name, case_type, attorney_user_id, created_by,
             phase, status,
             assigned_date, demand_deadline, demand_settled_date,
             demand_duration_days,
             litigation_start_date, litigation_settled_date, litigation_duration_days,
             presuit_offer, resolution_type, fee_rate,
             uim_start_date, uim_demand_out_date, uim_negotiate_date, uim_settled_date, uim_duration_days,
             is_policy_limit,
             settled, difference, legal_fee, discounted_legal_fee,
             uim_settled, uim_legal_fee, uim_discounted_legal_fee, uim_commission,
             commission, commission_type,
             month, check_received, is_marketing,
             submitted_at, deleted_at,
             demand_out_date, negotiate_date,
             top_offer_amount, top_offer_date)
            VALUES (?,?,?,?,?, ?,?, ?,?,?, ?, ?,?,?, ?,?,?, ?,?,?,?,?, ?, ?,?,?,?, ?,?,?,?, ?,?, ?,?,?, ?,?, ?,?, ?,?)");
        $stmt->execute([
            $ac['case_number'], $ac['client_name'], $ac['case_type'] ?? 'Auto',
            $cmcUserId, $cmcUserId,
            $phase, $status,
            $ac['assigned_date'] ?? null, $ac['demand_deadline'] ?? null, $ac['demand_settled_date'] ?? null,
            $ac['demand_duration_days'] ?? null,
            $ac['litigation_start_date'] ?? null, $ac['litigation_settled_date'] ?? null, $ac['litigation_duration_days'] ?? null,
            $ac['presuit_offer'] ?? 0, $resolutionType, $feeRate,
            $ac['uim_start_date'] ?? null, $ac['uim_demand_out_date'] ?? null, $ac['uim_negotiate_date'] ?? null, $ac['uim_settled_date'] ?? null, $ac['uim_duration_days'] ?? null,
            $ac['is_policy_limit'] ?? 0,
            $ac['settled'] ?? 0, $ac['difference'] ?? 0, $ac['legal_fee'] ?? 0, $ac['discounted_legal_fee'] ?? 0,
            $ac['uim_settled'] ?? null, $ac['uim_legal_fee'] ?? null, $ac['uim_discounted_legal_fee'] ?? null, $ac['uim_commission'] ?? null,
            $ac['commission'] ?? 0, $ac['commission_type'] ?? null,
            $ac['month'] ?? null, $ac['check_received'] ?? 0, $ac['is_marketing'] ?? 0,
            $ac['submitted_at'], $ac['deleted_at'] ?? null,
            $ac['demand_out_date'] ?? null, $ac['negotiate_date'] ?? null,
            $ac['top_offer_amount'] ?? null, $ac['top_offer_date'] ?? null,
        ]);
        $attInserted++;
    } catch (PDOException $e) {
        $attErrors++;
        if ($attErrors <= 3) msg("    Error: " . $e->getMessage(), 'err');
    }
}
$errStr = $attErrors > 0 ? " ({$attErrors} errors)" : '';
msg("  attorney_cases: {$attInserted}/" . count($attCases) . " inserted{$errStr}", $attErrors > 0 ? 'warn' : 'ok');
$stats['attorney_cases'] = ['status' => $attErrors > 0 ? 'partial' : 'ok', 'count' => $attInserted, 'total' => count($attCases), 'errors' => $attErrors];

// Step 5: Commission remaining tables (with user ID mapping)
msg('  Migrating remaining Commission tables...', 'info');

// litigation_cases
copyTable($comm, $cmc, 'litigation_cases', null, function($row) use ($userIdMap) {
    $row['user_id'] = $userIdMap[$row['user_id']] ?? $row['user_id'];
    return $row;
});

// referral_entries
copyTable($comm, $cmc, 'referral_entries', null, function($row) use ($userIdMap) {
    if (isset($row['case_manager_id']) && $row['case_manager_id']) {
        $row['case_manager_id'] = $userIdMap[$row['case_manager_id']] ?? $row['case_manager_id'];
    }
    if (isset($row['lead_id']) && $row['lead_id']) {
        $row['lead_id'] = $userIdMap[$row['lead_id']] ?? $row['lead_id'];
    }
    if (isset($row['created_by']) && $row['created_by']) {
        $row['created_by'] = $userIdMap[$row['created_by']] ?? $row['created_by'];
    }
    return $row;
});

// traffic_cases
copyTable($comm, $cmc, 'traffic_cases', null, function($row) use ($userIdMap) {
    $row['user_id'] = $userIdMap[$row['user_id']] ?? $row['user_id'];
    if (isset($row['requested_by']) && $row['requested_by']) {
        $row['requested_by'] = $userIdMap[$row['requested_by']] ?? $row['requested_by'];
    }
    return $row;
});

// traffic_case_files
copyTable($comm, $cmc, 'traffic_case_files', null, function($row) use ($userIdMap) {
    if (isset($row['uploaded_by']) && $row['uploaded_by']) {
        $row['uploaded_by'] = $userIdMap[$row['uploaded_by']] ?? $row['uploaded_by'];
    }
    return $row;
});

// demand_requests
copyTable($comm, $cmc, 'demand_requests', null, function($row) use ($userIdMap) {
    if (isset($row['requested_by']) && $row['requested_by']) {
        $row['requested_by'] = $userIdMap[$row['requested_by']] ?? $row['requested_by'];
    }
    if (isset($row['assigned_to']) && $row['assigned_to']) {
        $row['assigned_to'] = $userIdMap[$row['assigned_to']] ?? $row['assigned_to'];
    }
    return $row;
});

// traffic_requests
copyTable($comm, $cmc, 'traffic_requests', null, function($row) use ($userIdMap) {
    if (isset($row['requested_by']) && $row['requested_by']) {
        $row['requested_by'] = $userIdMap[$row['requested_by']] ?? $row['requested_by'];
    }
    if (isset($row['assigned_to']) && $row['assigned_to']) {
        $row['assigned_to'] = $userIdMap[$row['assigned_to']] ?? $row['assigned_to'];
    }
    return $row;
});

// deadline_extension_requests
copyTable($comm, $cmc, 'deadline_extension_requests', null, function($row) use ($userIdMap) {
    if (isset($row['user_id']) && $row['user_id']) {
        $row['user_id'] = $userIdMap[$row['user_id']] ?? $row['user_id'];
    }
    if (isset($row['reviewed_by']) && $row['reviewed_by']) {
        $row['reviewed_by'] = $userIdMap[$row['reviewed_by']] ?? $row['reviewed_by'];
    }
    // Remove case_id if it references Commission DB cases (not CMC cases)
    if (isset($row['case_id'])) $row['case_id'] = null;
    return $row;
});

// employee_goals
copyTable($comm, $cmc, 'employee_goals', null, function($row) use ($userIdMap) {
    if (isset($row['user_id']) && $row['user_id']) {
        $row['user_id'] = $userIdMap[$row['user_id']] ?? $row['user_id'];
    }
    if (isset($row['created_by']) && $row['created_by']) {
        $row['created_by'] = $userIdMap[$row['created_by']] ?? $row['created_by'];
    }
    return $row;
});

// performance_snapshots
copyTable($comm, $cmc, 'performance_snapshots', null, function($row) use ($userIdMap) {
    if (isset($row['employee_id']) && $row['employee_id']) {
        $row['employee_id'] = $userIdMap[$row['employee_id']] ?? $row['employee_id'];
    }
    // Remove extra columns that may exist in Commission but not in CMC
    unset($row['avg_total_days'], $row['demand_resolution_rate'],
          $row['deadline_compliance_rate'], $row['avg_days_before_deadline'],
          $row['overdue_cases_count'], $row['active_cases_count'],
          $row['max_concurrent_cases'], $row['capacity_score']);
    return $row;
});

// manager_team
copyTable($comm, $cmc, 'manager_team', null, function($row) use ($userIdMap) {
    if (isset($row['manager_id']) && $row['manager_id']) {
        $row['manager_id'] = $userIdMap[$row['manager_id']] ?? $row['manager_id'];
    }
    if (isset($row['employee_id']) && $row['employee_id']) {
        $row['employee_id'] = $userIdMap[$row['employee_id']] ?? $row['employee_id'];
    }
    return $row;
});

// ════════════════════════════════════════
// PHASE 7: Apply Migration 004 columns (if needed)
// ════════════════════════════════════════
phase('Phase 7: Schema updates (Migration 004)');

// Check if 'team' column already exists on users table
$teamColExists = false;
try {
    $cols = $cmc->query("SHOW COLUMNS FROM users LIKE 'team'")->fetchAll();
    $teamColExists = count($cols) > 0;
} catch (PDOException $e) {}

if (!$teamColExists) {
    msg('  Applying migration 004 schema changes...', 'info');
    try {
        // Add team to users
        $cmc->exec("ALTER TABLE users ADD COLUMN team VARCHAR(50) NULL AFTER role");
        $cmc->exec("CREATE INDEX idx_users_team ON users(team)");
        msg('  users.team column added', 'ok');
    } catch (PDOException $e) {
        msg('  users.team: ' . $e->getMessage(), 'warn');
    }

    try {
        // Update cases.status ENUM to new codes
        $cmc->exec("ALTER TABLE cases MODIFY COLUMN status ENUM(
            'ini','rec','verification','rfd','neg',
            'final_verification','accounting','closed'
        ) NOT NULL DEFAULT 'ini'");
        msg('  cases.status ENUM updated (new status codes)', 'ok');
    } catch (PDOException $e) {
        msg('  cases.status: ' . $e->getMessage(), 'warn');
    }

    // Add client contact fields
    $addCols = [
        "ALTER TABLE cases ADD COLUMN client_phone VARCHAR(20) NULL AFTER client_dob",
        "ALTER TABLE cases ADD COLUMN client_email VARCHAR(255) NULL AFTER client_phone",
        "ALTER TABLE cases ADD COLUMN prelitigation_start_date DATE NULL",
        "ALTER TABLE cases ADD COLUMN sent_to_billing_date DATE NULL",
        "ALTER TABLE cases ADD COLUMN sent_to_attorney_date DATE NULL",
        "ALTER TABLE cases ADD COLUMN sent_to_billing_final_date DATE NULL",
        "ALTER TABLE cases ADD COLUMN sent_to_accounting_date DATE NULL",
        "ALTER TABLE cases ADD COLUMN closed_date DATE NULL",
        "ALTER TABLE cases ADD COLUMN file_location VARCHAR(500) NULL",
    ];
    foreach ($addCols as $sql) {
        try {
            $cmc->exec($sql);
        } catch (PDOException $e) {
            // Column may already exist
        }
    }
    msg('  cases: client_phone, client_email, workflow date columns added', 'ok');

    // Create prelitigation_followups if not exists
    try {
        $cmc->exec("CREATE TABLE IF NOT EXISTS prelitigation_followups (
            id INT AUTO_INCREMENT PRIMARY KEY,
            case_id INT NOT NULL,
            followup_date DATE NOT NULL,
            followup_type ENUM('phone','email','text','in_person','other') NOT NULL DEFAULT 'phone',
            contact_result ENUM('reached','voicemail','no_answer','callback_scheduled','treatment_update','text') NOT NULL DEFAULT 'reached',
            treatment_status_update VARCHAR(255) NULL,
            next_followup_date DATE NULL,
            notes TEXT NULL,
            created_by INT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_prelit_case (case_id),
            INDEX idx_prelit_next (next_followup_date)
        ) ENGINE=InnoDB");
        msg('  prelitigation_followups table ensured', 'ok');
    } catch (PDOException $e) {
        msg('  prelitigation_followups: ' . $e->getMessage(), 'warn');
    }

    // Create accounting_disbursements if not exists
    try {
        $cmc->exec("CREATE TABLE IF NOT EXISTS accounting_disbursements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            case_id INT NOT NULL,
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
        ) ENGINE=InnoDB");
        msg('  accounting_disbursements table ensured', 'ok');
    } catch (PDOException $e) {
        msg('  accounting_disbursements: ' . $e->getMessage(), 'warn');
    }
} else {
    msg('  Migration 004 already applied (team column exists) — SKIPPED', 'info');
}

// ════════════════════════════════════════
// RE-ENABLE FK CHECKS
// ════════════════════════════════════════
$cmc->exec('SET FOREIGN_KEY_CHECKS = 1');
msg('<br>Foreign key checks re-enabled', 'info');

// ════════════════════════════════════════
// FINAL REPORT
// ════════════════════════════════════════
echo '<br>';
phase('Migration Complete — Summary');

$totalInserted = 0;
$totalErrors = 0;
$failed = [];

echo '<div class="summary"><pre>';
echo str_pad('Table', 30) . str_pad('Status', 10) . str_pad('Rows', 10) . "Notes\n";
echo str_repeat('─', 70) . "\n";

foreach ($stats as $table => $s) {
    $statusStr = $s['status'];
    $countStr = isset($s['count']) ? $s['count'] : '-';
    $notes = '';

    if ($s['status'] === 'ok') {
        $notes = '';
        $totalInserted += $s['count'];
    } elseif ($s['status'] === 'partial') {
        $notes = "{$s['errors']} errors";
        $totalInserted += $s['count'];
        $totalErrors += $s['errors'];
    } elseif ($s['status'] === 'skipped') {
        $notes = $s['reason'] ?? '';
    } elseif ($s['status'] === 'empty') {
        $notes = 'source empty';
    } elseif ($s['status'] === 'failed') {
        $notes = $s['error'] ?? 'unknown';
        $failed[] = $table;
    }

    echo str_pad($table, 30) . str_pad($statusStr, 10) . str_pad($countStr, 10) . $notes . "\n";
}

echo str_repeat('─', 70) . "\n";
echo "Total rows inserted: {$totalInserted}\n";
if ($totalErrors > 0) echo "Total errors: {$totalErrors}\n";
if (!empty($failed)) echo "Failed tables: " . implode(', ', $failed) . "\n";
echo '</pre></div>';

if (empty($failed) && $totalErrors === 0) {
    echo '<div class="summary"><span class="ok">✓ Migration completed successfully!</span></div>';
} elseif (!empty($failed)) {
    echo '<div class="summary fail-summary"><span class="err">⚠ Migration completed with failures. Check errors above.</span></div>';
} else {
    echo '<div class="summary"><span class="warn">⚠ Migration completed with some errors. Review warnings above.</span></div>';
}

echo '</body></html>';
