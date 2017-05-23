ALTER TABLE article ADD COLUMN `int_page_read_count` int(10) unsigned NOT NULL DEFAULT 0 AFTER `msg_data_id`;
ALTER TABLE article ADD COLUMN `add_to_fav_count` int(10) unsigned NOT NULL DEFAULT 0 AFTER `msg_data_id`;


ALTER TABLE article ADD COLUMN `mass_id` int(10) unsigned NOT NULL DEFAULT 0 AFTER `parent_id`;
