<?php
/**
 * Application Configuration
 */

define('APP_NAME', $_ENV['APP_NAME'] ?? 'CMC');
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '1.0.0');
define('BASE_PATH', dirname(dirname(__DIR__)));
define('BACKEND_PATH', dirname(__DIR__));
define('FRONTEND_PATH', BASE_PATH . '/frontend');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Session
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 28800));
define('SESSION_NAME', $_ENV['SESSION_NAME'] ?? 'CMC_SESSION');

// Pagination (large default = show all on one page)
define('ITEMS_PER_PAGE', 10000);
define('MAX_ITEMS_PER_PAGE', 10000);

// MR Defaults
define('DEFAULT_FOLLOWUP_DAYS', 7);
define('DEADLINE_WARNING_DAYS', 7);
define('DEFAULT_DEADLINE_DAYS', 30);
define('ADMIN_ESCALATION_DAYS_AFTER_DEADLINE', 14);

// Commission Rates
define('FEE_RATE_STANDARD', 33.33);
define('FEE_RATE_PREMIUM', 40.00);
define('COMMISSION_RATE_DEFAULT', 10.00);
define('COMMISSION_RATE_MIN', 0);
define('COMMISSION_RATE_MAX', 20.00);
define('MARKETING_COMMISSION_RATE', 5.00);

// Traffic Commission
define('TRAFFIC_COMMISSION_DISMISSED', 150.00);
define('TRAFFIC_COMMISSION_AMENDED', 100.00);

// Workflow phase deadlines (days)
define('PRELITIGATION_FOLLOWUP_DAYS', 21);  // 3-week follow-up cycle
define('BILLING_COLLECTION_DAYS', 28);      // 4 weeks to collect records
define('ATTORNEY_DEMAND_DAYS', 90);         // 90 days to get top offer
define('BILLING_FINAL_DAYS', 14);           // 2 weeks for final balance check
define('ACCOUNTING_DISBURSE_DAYS', 7);      // 1 week to disburse

// Status-to-owner auto-assignment (MR cases)
define('STATUS_OWNER_MAP', [
    'ini'                 => 2,
    'rec'                 => 1,
    'verification'        => 4,
    'rfd'                 => 4,
    'neg'                 => 1,
    'lit'                 => 4,
    'final_verification'  => 4,
    'accounting'          => 6,
    'closed'              => 3,
]);

// Card owner mapping (bank reconciliation)
define('CARD_OWNER_MAP', [
    '9027' => 'Sunny',
    '8433' => 'Soyong',
    '2443' => 'Jimi',
    '2518' => 'Karl',
    '3052' => 'Miki',
    '3060' => 'Ella',
    '3128' => 'Dave',
    '2984' => 'Chloe',
]);

date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/New_York');
