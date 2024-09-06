CREATE TABLE `__stat_level` (
`stl_id` INT(10) NOT NULL AUTO_INCREMENT,
`stl_st_id` INT(10) NULL DEFAULT NULL,
`stl_lvl` INT(10) NOT NULL DEFAULT '1',
`stl_current_exp` DECIMAL(20,2) NOT NULL DEFAULT '0.00',
`stl_next_exp` DECIMAL(20,2) NOT NULL DEFAULT '100.00',
`stl_multiplier` DECIMAL(20,2) NOT NULL DEFAULT '1.50',
PRIMARY KEY (`stl_id`) USING BTREE,
INDEX `FK____stat_lvl` (`stl_st_id`) USING BTREE,
CONSTRAINT `FK____stat_lvl` FOREIGN KEY (`stl_st_id`) REFERENCES `__stat` (`st_id`) ON UPDATE NO ACTION ON DELETE CASCADE
)
    COLLATE='utf8mb3_general_ci'
ENGINE=InnoDB
;

