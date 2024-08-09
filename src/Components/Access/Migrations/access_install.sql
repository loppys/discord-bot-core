CREATE TABLE IF NOT EXISTS `access` (
`ac_id` INT(11) NOT NULL AUTO_INCREMENT,
`ac_usr_id` VARCHAR(400) NOT NULL COLLATE 'utf8_general_ci',
`ac_group_lvl` INT(11) NOT NULL DEFAULT '0',
`ac_update_date` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
PRIMARY KEY (`ac_id`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
