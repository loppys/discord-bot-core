CREATE TABLE `__stat` (
`st_id` INT(11) NOT NULL AUTO_INCREMENT,
`st_name` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
`st_type` INT(11) NOT NULL,
`st_value` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`st_usr_id` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`st_srv_id` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
PRIMARY KEY (`st_id`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=2
;
