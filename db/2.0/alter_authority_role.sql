ALTER TABLE authority_role ADD COLUMN `role_type` SMALLINT(6) NOT NULL DEFAULT 0 AFTER `is_super_admin`;
ALTER TABLE authority_role ADD COLUMN `role_level` SMALLINT(6) NOT NULL DEFAULT 0 AFTER `role_type`;
