DROP DATABASE IF EXISTS `TRS1`
CREATE DATABASE `TRS1` ;

CREATE TABLE `TRS1`.`currentbookings` (
`HRID` VARCHAR( 200 ) NOT NULL ,
`CBID` BIGINT NOT NULL ,
`UserID` VARCHAR( 200 ) NOT NULL ,
`Status` INT NOT NULL ,
`StartTime` BIGINT NOT NULL ,
`EndTime` BIGINT NOT NULL ,
`GuestNum` INT NOT NULL ,
`GuestNames` VARCHAR( 200 ) NULL ,
`GuestContactNos` VARCHAR( 200 ) NULL ,
`GuestEmail` VARCHAR( 200 ) NULL ,
`Comment` TEXT NULL ,
`WaitingID` BIGINT NULL ,
`AdvanceID` BIGINT NULL ,
PRIMARY KEY ( `CBID` )
) ENGINE = InnoDB ;

ALTER TABLE `currentbookings` CHANGE `StartTime` `StartTime` DATETIME NOT NULL ; 
ALTER TABLE `currentbookings` CHANGE `EndTime` `EndTime` DATETIME NOT NULL ;
ALTER TABLE `currentbookings` ADD `ExpectedDuration` INT NOT NULL COMMENT 'in minutes' AFTER `EndTime` ;

ALTER TABLE `currentbookings` DROP `GuestNames` ,
DROP `GuestContactNos` ,
DROP `GuestEmail` ,
DROP `Comment` ; 

ALTER TABLE `currentbookings` ADD `GuestUID` INT NULL DEFAULT NULL AFTER `GuestNum` ;
ALTER TABLE `currentbookings` ADD `Notes` TEXT NULL AFTER `GuestNum` ;

CREATE TABLE `trs1`.`waitinglist` (
`HRID` VARCHAR( 200 ) NOT NULL ,
`UserID` VARCHAR( 200 ) NOT NULL ,
`WaitID` BIGINT NOT NULL ,
`Status` INT NOT NULL ,
`StartTime` DATETIME NOT NULL ,
`EndTime` DATETIME NOT NULL ,
`GuestNum` INT NOT NULL ,
`GuestNames` VARCHAR( 200 ) NULL ,
`GuestContactNos` VARCHAR( 200 ) NULL ,
`GuestEmail` VARCHAR( 200 ) NULL ,
`Comment` VARCHAR( 200 ) NULL ,
`AdvanceID` BIGINT NULL ,
PRIMARY KEY ( `WaitID` )
) ENGINE = InnoDB;

ALTER TABLE `waitinglist` ADD `ExpectedDuration` INT NOT NULL COMMENT 'in minutes' AFTER `EndTime` ;

ALTER TABLE `waitinglist` DROP `GuestNames` ,
DROP `GuestContactNos` ,
DROP `GuestEmail` ,
DROP `Comment` ;

ALTER TABLE `waitinglist` ADD `GuestUID` INT NULL DEFAULT NULL AFTER `GuestNum` ;
ALTER TABLE `waitinglist` ADD `Notes` TEXT NULL AFTER `GuestNum` ;

CREATE TABLE `trs1`.`guests` (
`UID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`HRID` VARCHAR( 200 ) NOT NULL ,
`PrimaryContactNumber` BIGINT NULL ,
`AlternateContactNumber` VARCHAR( 400 ) NULL ,
`Name` VARCHAR( 200 ) NULL ,
`Email` VARCHAR( 200 ) NULL ,
`Comment` VARCHAR( 600 ) NULL
) ENGINE = InnoDB;

CREATE TABLE `TRS1`.`cbtables` (
`HRID` VARCHAR( 200 ) NOT NULL ,
`TBID` VARCHAR( 200 ) NOT NULL ,
`CBID` BIGINT NOT NULL ,
`StartTime` DATETIME NOT NULL ,
`EndTime` DATETIME NOT NULL ,
PRIMARY KEY ( `TBID` ) ,
INDEX ( `CBID` )
) ENGINE = InnoDB ;

ALTER TABLE `cbtables` DROP PRIMARY KEY ,
ADD PRIMARY KEY (`HRID` , `TBID` , `CBID` ) ;

ALTER TABLE `cbtables` CHANGE `StartTime` `StartTime` DATETIME NOT NULL ,
CHANGE `EndTime` `EndTime` DATETIME NOT NULL ;

CREATE TABLE `trs1`.`advancebookings` (
`HRID` VARCHAR( 200 ) NOT NULL ,
`ABID` BIGINT NOT NULL ,
`ABbyUser` VARCHAR( 200 ) NOT NULL ,
`ABActionUser` VARCHAR( 200 ) NOT NULL ,
`ABStatus` INT NOT NULL ,
`ABTableStatus` INT NOT NULL ,
`ABonDateTime` DATETIME NOT NULL ,
`ABforDateTime` DATETIME NOT NULL ,
`GuestNum` INT NULL ,
`GuestNames` VARCHAR( 200 ) NULL ,
`GuestContactNos` VARCHAR( 200 ) NULL ,
`GuestEmail` VARCHAR( 200 ) NULL ,
`GuestComment` INT NULL ,
PRIMARY KEY ( `HRID` , `ABID` )
) ENGINE = InnoDB ;

ALTER TABLE `advancebookings` CHANGE `ABbyUser` `BookingMethod` VARCHAR( 200 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
CHANGE `ABActionUser` `UserID` VARCHAR( 200 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL

ALTER TABLE `advancebookings` CHANGE `ABStatus` `Status` INT( 11 ) NOT NULL ,
CHANGE `ABTableStatus` `TableStatus` INT( 11 ) NOT NULL ,
CHANGE `ABonDateTime` `OnDateTime` DATETIME NOT NULL ,
CHANGE `ABforDateTime` `ForDateTime` DATETIME NOT NULL 

ALTER TABLE `advancebookings` ADD `ExpectedDuration` INT NOT NULL AFTER `ForDateTime`
ALTER TABLE `advancebookings` DROP `TableStatus`

ALTER TABLE `advancebookings` DROP `GuestNames` ,
DROP `GuestContactNos` ,
DROP `GuestEmail` ,
DROP `GuestComment` ;
 
ALTER TABLE `advancebookings` ADD `GuestUID` INT NULL DEFAULT NULL AFTER `GuestNum` ;

CREATE TABLE `trs1`.`abtables` (
`ABTUID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`HRID` VARCHAR( 200 ) NOT NULL ,
`ABID` BIGINT NOT NULL ,
`TBID` VARCHAR( 200 ) NOT NULL ,
`Alive` INT NOT NULL
) ENGINE = INNODB;

ALTER TABLE `abtables` CHANGE `Alive` `Alive` BIT( 1 ) NOT NULL;
ALTER TABLE `abtables` ENGINE = InnoDB;

CREATE TABLE `trs1`.`restables` (
`HRID` VARCHAR( 200 ) NOT NULL ,
`TBID` VARCHAR( 200 ) NOT NULL ,
`Capacity` INT NOT NULL ,
`Status` INT NOT NULL ,
`OnlineStatus` INT NOT NULL ,
PRIMARY KEY ( `HRID` , `TBID` )
) ENGINE = InnoDB; 

ALTER TABLE `restables` CHANGE `Capacity` `MinCapacity` INT( 11 ) NOT NULL 
ALTER TABLE `restables` ADD `MaxCapacity` INT NOT NULL AFTER `MinCapacity` 
ALTER TABLE `restables` ADD `DisplayName` VARCHAR(100) NOT NULL AFTER `TBID`
 
INSERT INTO `trs1`.`restables` (`HRID`, `TBID`, `DisplayName`, `MinCapacity`, `MaxCapacity`, `Status`, `OnlineStatus`) VALUES ('MS1', 'TB1', 'Table-1', '2', '3', '1456', '5432'), ('MS1', 'TB2', 'Table-2', '3', '5', '1456', '5432'),('MS1', 'TB3', 'Table-3', '4', '5', '1456', '5432'),('MS1', 'TB4', 'Table-4', '2', '4', '1456', '5432'),('MS1', 'TB5', 'Table-5', '3', '5', '1456', '5432');