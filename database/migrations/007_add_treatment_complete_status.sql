-- Migration 007: Add treatment_complete intermediate status
-- treatment_complete = treatment done, waiting for other providers before billing activation

ALTER TABLE case_providers MODIFY COLUMN overall_status
    ENUM('treating','treatment_complete','not_started','requesting','follow_up','action_needed',
         'received_partial','on_hold','received_complete','verified')
    NOT NULL DEFAULT 'not_started';
