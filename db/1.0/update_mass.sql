ALTER TABLE mass ADD COLUMN `fail_times` smallint(4) unsigned NOT NULL DEFAULT 0 AFTER `msg_status`;
