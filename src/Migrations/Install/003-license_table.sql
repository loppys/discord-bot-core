CREATE TABLE `license` (
`lns_id` INT(11) NOT NULL AUTO_INCREMENT,
`lns_guild` VARCHAR(75) NOT NULL DEFAULT '' COLLATE 'utf16_general_ci',
`lns_universe` TINYINT(4) NOT NULL DEFAULT '0',
`lns_key` VARCHAR(150) NOT NULL DEFAULT '' COLLATE 'utf16_general_ci',
`lns_component_class` VARCHAR(150) NOT NULL DEFAULT '' COLLATE 'utf16_general_ci',
`lns_component_name` VARCHAR(150) NOT NULL DEFAULT '' COLLATE 'utf16_general_ci',
`lns_use_component_class` TINYINT(4) NOT NULL DEFAULT '0',
`lns_expired` TINYINT(4) NOT NULL DEFAULT '0',
`lns_infinity` TINYINT(4) NOT NULL DEFAULT '0',
`lns_trial` TINYINT(4) NOT NULL DEFAULT '0',
`lns_master` TINYINT(4) NOT NULL DEFAULT '0',
`lns_time_end` TIMESTAMP NULL DEFAULT NULL,
`lns_time_activate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
PRIMARY KEY (`lns_id`) USING BTREE,
UNIQUE INDEX `lns_key` (`lns_key`) USING BTREE,
INDEX `lns_guild` (`lns_guild`, `lns_key`) USING BTREE
)
COLLATE='utf16_general_ci'
ENGINE=InnoDB
;
