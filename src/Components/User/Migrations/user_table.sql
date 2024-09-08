CREATE TABLE IF NOT EXISTS `users` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`usr_id` VARCHAR(255) NOT NULL COLLATE 'utf8_general_ci',
`usr_hidden` INT(11) NOT NULL DEFAULT '0',
`usr_system` INT(11) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
