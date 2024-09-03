CREATE TABLE IF NOT EXISTS `migrations` (
`mig_id` INT NOT NULL AUTO_INCREMENT,
`mig_file` VARCHAR(400) NOT NULL DEFAULT '',
`mig_hash` VARCHAR(400) NOT NULL DEFAULT '',
`mig_query` LONGTEXT DEFAULT NULL,
`mig_date` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
`mig_update_date` TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
PRIMARY KEY (`mig_id`)
)
COLLATE='utf8_general_ci'
;
