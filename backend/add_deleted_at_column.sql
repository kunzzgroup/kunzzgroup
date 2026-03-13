-- add_deleted_at_column.sql
-- Run this script on your MySQL database to add the necessary columns for soft delete.

ALTER TABLE j1stockedit_data ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL;
ALTER TABLE j1stockedit_data ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50) NULL;

ALTER TABLE j2stockedit_data ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL;
ALTER TABLE j2stockedit_data ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50) NULL;

ALTER TABLE j3stockedit_data ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL;
ALTER TABLE j3stockedit_data ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50) NULL;

ALTER TABLE stockinout_data ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL;
ALTER TABLE stockinout_data ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50) NULL;

ALTER TABLE j1stockinout_data ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL;
ALTER TABLE j1stockinout_data ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50) NULL;

ALTER TABLE j2stockinout_data ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL;
ALTER TABLE j2stockinout_data ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50) NULL;

ALTER TABLE j3stockinout_data ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL;
ALTER TABLE j3stockinout_data ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50) NULL;

-- Mobile tables
ALTER TABLE j1stockeditmobile_data ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL;
ALTER TABLE j1stockeditmobile_data ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50) NULL;

ALTER TABLE j2stockeditmobile_data ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL;
ALTER TABLE j2stockeditmobile_data ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50) NULL;

ALTER TABLE j3stockeditmobile_data ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL;
ALTER TABLE j3stockeditmobile_data ADD COLUMN IF NOT EXISTS deleted_by VARCHAR(50) NULL;
