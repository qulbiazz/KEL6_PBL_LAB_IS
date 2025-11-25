-- Add versioning columns to about_settings table
ALTER TABLE about_settings 
ADD COLUMN IF NOT EXISTS version_name VARCHAR(255) DEFAULT 'Default Version',
ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT false,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Set first record as active if no active version exists
UPDATE about_settings 
SET is_active = true 
WHERE id = (SELECT MIN(id) FROM about_settings)
AND NOT EXISTS (SELECT 1 FROM about_settings WHERE is_active = true);
