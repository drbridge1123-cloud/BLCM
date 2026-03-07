-- Migration 011: Add start_date and end_date to case_tasks
ALTER TABLE case_tasks
    ADD COLUMN start_date DATE NULL AFTER due_date,
    ADD COLUMN end_date DATE NULL AFTER start_date;
