

ALTER SCHEMA IF NOT EXISTS `cshop` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `cshop` ;


-- -----------------------------------------------------
-- Table `cshop`.`option`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `@{prefix}@option` (
  `key` VARCHAR(120) NOT NULL,
  `category` VARCHAR(120) NOT NULL,
  `value` VARCHAR(120) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY (`key`, `category`))
ENGINE = InnoDB;
/*    





					my changes  
					

*/
insert into `@{prefix}@plugin` (`key`)
select (`key`) from `@{prefix}@plugin_meta`
;

insert into `@{prefix}@option`  (`value`)
select (`value`) from `@{prefix}@plugin_meta`
;

insert into `@{prefix}@option` (`category`)
select `class` from `@{prefix}@plugin` inner join `@{prefix}@plugin_meta`
where `@{prefix}@plugin`.id = `@{prefix}@plugin_meta`.pluginid
;
													
													
													
