CREATE TABLE `settings_log` (
`stl_id` INT(11) NOT NULL AUTO_INCREMENT,
`stl_before` TEXT(32767) NULL DEFAULT NULL COLLATE 'utf16_general_ci',
`stl_after` TEXT(32767) NULL DEFAULT NULL COLLATE 'utf16_general_ci',
PRIMARY KEY (`stl_id`) USING BTREE
)
COLLATE='utf16_general_ci'
ENGINE=InnoDB
;
