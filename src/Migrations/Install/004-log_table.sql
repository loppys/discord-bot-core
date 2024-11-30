CREATE TABLE IF NOT EXISTS `_logs` (
`lg_value` MEDIUMTEXT NULL DEFAULT NULL COLLATE 'utf16_general_ci',
`lg_source` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf16_general_ci',
`lg_time` TIMESTAMP NULL DEFAULT current_timestamp()
)
COLLATE='utf16_general_ci'
ENGINE=InnoDB
;
