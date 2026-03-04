-- Migration 006: Add 'treating' to case_providers.overall_status ENUM
-- Providers added during treatment phase should start as 'treating'

ALTER TABLE case_providers MODIFY COLUMN overall_status
    ENUM('treating','not_started','requesting','follow_up','action_needed',
         'received_partial','on_hold','received_complete','verified')
    NOT NULL DEFAULT 'not_started';
