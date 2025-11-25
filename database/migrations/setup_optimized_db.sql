-- Quick Setup Script
-- Run this single file to setup optimized database structure and migrate data

\echo '========================================='
\echo 'BORROWING SYSTEM - DATABASE OPTIMIZATION'
\echo '========================================='
\echo ''

-- Stop on error
\set ON_ERROR_STOP on

-- Show execution time
\timing on

\echo 'Step 1: Creating optimized structure...'
\i migration_optimized_structure.sql

\echo ''
\echo 'Step 2: Migrating existing data...'
\i migrate_existing_data_simple.sql

\echo ''
\echo '========================================='
\echo 'SETUP COMPLETE!'
\echo '========================================='
\echo ''
\echo 'Please verify the migration:'
\echo '1. Check views: SELECT * FROM borrowing_details_view LIMIT 5;'
\echo '2. Check equipment: SELECT * FROM equipment_availability_view;'
\echo '3. Check rooms: SELECT * FROM room_availability_view;'
\echo '4. Backup table created: borrowings_backup'
\echo ''
