CREATE TABLE `commands` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`name` VARCHAR(100) NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
`access` INT(11) NOT NULL DEFAULT '0',
`scheme` ENUM('N','O') NOT NULL DEFAULT 'N' COMMENT 'N - новая, O - старая' COLLATE 'utf8_general_ci',
`class` VARCHAR(600) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
`description` VARCHAR(600) NOT NULL DEFAULT 'Нет описания' COLLATE 'utf8_general_ci',
PRIMARY KEY (`id`) USING BTREE
)
    COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
