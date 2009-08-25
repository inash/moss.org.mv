-- MySQL dump 10.11
--
-- Host: localhost    Database: moss
-- ------------------------------------------------------
-- Server version	5.0.75-0ubuntu10.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `fees`
--

DROP TABLE IF EXISTS `fees`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `fees` (
  `feeId` int(11) NOT NULL auto_increment,
  `timestamp` datetime NOT NULL,
  `userId` varchar(50) NOT NULL,
  `forTheYear` int(4) NOT NULL,
  `currency` varchar(3) NOT NULL default 'MRF',
  `amount` decimal(6,2) NOT NULL,
  PRIMARY KEY  (`feeId`),
  KEY `FK_fees_1` (`userId`),
  CONSTRAINT `FK_fees_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `groups` (
  `name` varchar(45) NOT NULL,
  `title` varchar(45) NOT NULL,
  `description` varchar(255) default NULL,
  `parent` varchar(45) default NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `logs` (
  `logId` bigint(20) NOT NULL auto_increment,
  `entity` varchar(50) NOT NULL,
  `entityId` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL,
  `code` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `userId` varchar(50) NOT NULL,
  PRIMARY KEY  (`logId`),
  KEY `FK_logs_1` (`userId`),
  CONSTRAINT `FK_logs_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `member_types`
--

DROP TABLE IF EXISTS `member_types`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `member_types` (
  `title` varchar(50) NOT NULL,
  PRIMARY KEY  (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `menu_groups`
--

DROP TABLE IF EXISTS `menu_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `menu_groups` (
  `name` varchar(45) NOT NULL,
  `title` varchar(45) NOT NULL,
  `description` varchar(100) default NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `menus` (
  `menuId` int(11) NOT NULL auto_increment,
  `menuGroup` varchar(45) default NULL,
  `userGroup` varchar(45) NOT NULL,
  `moduleName` varchar(45) NOT NULL,
  `order` int(10) NOT NULL,
  PRIMARY KEY  (`menuId`),
  KEY `FK_menus_groups` (`userGroup`),
  KEY `FK_menus_menu_groups` (`menuGroup`),
  KEY `FK_menus_modules` (`moduleName`),
  CONSTRAINT `FK_menus_menu_groups` FOREIGN KEY (`menuGroup`) REFERENCES `menu_groups` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_menus_groups` FOREIGN KEY (`userGroup`) REFERENCES `groups` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_menus_modules` FOREIGN KEY (`moduleName`) REFERENCES `modules` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `modules` (
  `name` varchar(45) NOT NULL,
  `title` varchar(45) NOT NULL,
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `page_revisions`
--

DROP TABLE IF EXISTS `page_revisions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `page_revisions` (
  `pageRevisionId` int(11) NOT NULL auto_increment,
  `pageId` int(11) NOT NULL,
  `userId` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL,
  `summary` varchar(100) default NULL,
  `body` text NOT NULL,
  PRIMARY KEY  (`pageRevisionId`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `pages` (
  `pageId` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateModified` datetime NOT NULL,
  `pageRevisionId` int(11) NOT NULL,
  `createdBy` varchar(50) NOT NULL,
  `modifiedBy` varchar(50) default NULL,
  `body` text NOT NULL,
  `table` text,
  `published` enum('Draft','Published') default 'Draft',
  PRIMARY KEY  (`pageId`),
  FULLTEXT KEY `title` (`title`,`body`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `permissions` (
  `permissionId` bigint(19) NOT NULL auto_increment,
  `entityType` enum('Group','User') NOT NULL default 'Group',
  `role` varchar(45) NOT NULL,
  `resource` varchar(45) NOT NULL,
  `permission` varchar(45) NOT NULL,
  PRIMARY KEY  (`permissionId`),
  KEY `FK_permissions_groups` (`role`),
  KEY `FK_permissions_modules` (`resource`),
  CONSTRAINT `FK_permissions_modules` FOREIGN KEY (`resource`) REFERENCES `modules` (`name`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users` (
  `userId` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `memberType` varchar(50) NOT NULL default 'User',
  `primaryGroup` varchar(45) NOT NULL default 'users',
  `website` varchar(100) default NULL,
  `company` varchar(100) default NULL,
  `location` varchar(50) default NULL,
  `dateRegistered` datetime NOT NULL,
  `dateLastLogin` datetime NOT NULL,
  `active` enum('Y','N') NOT NULL default 'N',
  `reset` enum('Y','N') NOT NULL default 'N',
  `disabled` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users_groups` (
  `ugId` int(11) NOT NULL auto_increment,
  `userId` varchar(50) NOT NULL,
  `group` varchar(45) NOT NULL,
  PRIMARY KEY  (`ugId`),
  KEY `FK_ug_1` (`userId`),
  KEY `FK_ug_2` (`group`),
  CONSTRAINT `FK_ug_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ug_2` FOREIGN KEY (`group`) REFERENCES `groups` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='InnoDB free: 6144 kB; (`group`) REFER `moss/groups`(`name`) ';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `users_new`
--

DROP TABLE IF EXISTS `users_new`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `users_new` (
  `unId` int(11) NOT NULL auto_increment,
  `userId` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY  (`unId`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-08-25  9:37:24
