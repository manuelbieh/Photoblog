ALTER TABLE `cel_photo_archive_settings` DROP `64`;
-- QUERY END

ALTER TABLE `cel_photo_archive_settings` ADD `key` VARCHAR( 64 ) NOT NULL AFTER `setting_id`;
-- QUERY END