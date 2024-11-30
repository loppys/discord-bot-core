CREATE TABLE IF NOT EXISTS `settings` (
`stg_id` INT(11) NOT NULL AUTO_INCREMENT,
`stg_guild` VARCHAR(255) NOT NULL COLLATE 'utf16_general_ci',
`stg_name` VARCHAR(100) NOT NULL COLLATE 'utf16_general_ci',
`stg_value` TEXT(32767) NULL DEFAULT NULL COLLATE 'utf16_general_ci',
`stg_type` ENUM('SELECT','TEXT','NUMBER','BOOL') NULL DEFAULT 'TEXT' COLLATE 'utf16_general_ci',
`stg_enabled` TINYINT(4) NOT NULL DEFAULT '1',
`stg_required` TINYINT(4) NOT NULL DEFAULT '0',
`stg_system` TINYINT(4) NOT NULL DEFAULT '0',
`stg_hidden` TINYINT(4) NOT NULL DEFAULT '0',
PRIMARY KEY (`stg_id`) USING BTREE,
INDEX `stg_guild_stg_name` (`stg_guild`, `stg_name`) USING BTREE
)
COLLATE='utf16_general_ci'
ENGINE=InnoDB
;
