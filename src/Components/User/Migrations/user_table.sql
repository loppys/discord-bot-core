CREATE TABLE `users` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`usr_id` VARCHAR(255) NOT NULL COLLATE 'utf8_general_ci',
`usr_stat_id` INT(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
