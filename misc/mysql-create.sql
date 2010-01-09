-- MySQL dump 10.13  Distrib 5.1.37, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: moss
-- ------------------------------------------------------
-- Server version	5.1.37-1ubuntu5

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
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fees` (
  `feeId` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL,
  `userId` varchar(50) NOT NULL,
  `forTheYear` int(4) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'MRF',
  `amount` decimal(6,2) NOT NULL,
  `enteredBy` varchar(50) NOT NULL,
  `entryMethod` varchar(50) NOT NULL,
  PRIMARY KEY (`feeId`),
  KEY `FK_fees_1` (`userId`),
  CONSTRAINT `FK_fees_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `name` varchar(45) NOT NULL,
  `title` varchar(45) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `parent` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `logId` bigint(20) NOT NULL AUTO_INCREMENT,
  `entity` varchar(50) NOT NULL,
  `entityId` varchar(20) NOT NULL,
  `timestamp` datetime NOT NULL,
  `code` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `userId` varchar(50) NOT NULL,
  PRIMARY KEY (`logId`)
) ENGINE=MyISAM AUTO_INCREMENT=87 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_types`
--

DROP TABLE IF EXISTS `member_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_types` (
  `title` varchar(50) NOT NULL,
  `fee` decimal(6,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_groups`
--

DROP TABLE IF EXISTS `menu_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_groups` (
  `name` varchar(45) NOT NULL,
  `title` varchar(45) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `menuId` int(11) NOT NULL AUTO_INCREMENT,
  `menuGroup` varchar(45) DEFAULT NULL,
  `userGroup` varchar(45) NOT NULL,
  `moduleName` varchar(45) NOT NULL,
  `order` int(10) NOT NULL,
  PRIMARY KEY (`menuId`),
  KEY `FK_menus_groups` (`userGroup`),
  KEY `FK_menus_menu_groups` (`menuGroup`),
  KEY `FK_menus_modules` (`moduleName`),
  CONSTRAINT `FK_menus_groups` FOREIGN KEY (`userGroup`) REFERENCES `groups` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_menus_menu_groups` FOREIGN KEY (`menuGroup`) REFERENCES `menu_groups` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_menus_modules` FOREIGN KEY (`moduleName`) REFERENCES `modules` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `name` varchar(45) NOT NULL,
  `title` varchar(45) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `moss_news`
--

DROP TABLE IF EXISTS `moss_news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `moss_news` (
  `newsId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(50) NOT NULL,
  `date` datetime NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`newsId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_revisions`
--

DROP TABLE IF EXISTS `page_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_revisions` (
  `pageRevisionId` int(11) NOT NULL AUTO_INCREMENT,
  `pageId` int(11) NOT NULL,
  `userId` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL,
  `summary` varchar(100) DEFAULT NULL,
  `body` text NOT NULL,
  PRIMARY KEY (`pageRevisionId`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `pageId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateModified` datetime NOT NULL,
  `pageRevisionId` int(11) NOT NULL,
  `createdBy` varchar(50) NOT NULL,
  `modifiedBy` varchar(50) DEFAULT NULL,
  `body` text NOT NULL,
  `table` text,
  `published` enum('Draft','Published') DEFAULT 'Draft',
  PRIMARY KEY (`pageId`),
  FULLTEXT KEY `title` (`title`,`body`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `permissionId` bigint(19) NOT NULL AUTO_INCREMENT,
  `entityType` enum('Group','User') NOT NULL DEFAULT 'Group',
  `role` varchar(45) NOT NULL,
  `resource` varchar(45) NOT NULL,
  `permission` varchar(45) NOT NULL,
  PRIMARY KEY (`permissionId`),
  KEY `FK_permissions_groups` (`role`),
  KEY `FK_permissions_modules` (`resource`),
  CONSTRAINT `FK_permissions_modules` FOREIGN KEY (`resource`) REFERENCES `modules` (`name`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `userId` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `memberType` varchar(50) NOT NULL DEFAULT 'User',
  `primaryGroup` varchar(50) NOT NULL DEFAULT 'Member',
  `website` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `dateRegistered` datetime NOT NULL,
  `dateLastLogin` datetime NOT NULL,
  `active` enum('Y','N') NOT NULL DEFAULT 'N',
  `reset` enum('Y','N') NOT NULL DEFAULT 'N',
  `disabled` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_groups` (
  `ugId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(50) NOT NULL,
  `group` varchar(45) NOT NULL,
  PRIMARY KEY (`ugId`),
  KEY `FK_ug_1` (`userId`),
  KEY `FK_ug_2` (`group`),
  CONSTRAINT `FK_ug_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ug_2` FOREIGN KEY (`group`) REFERENCES `groups` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='InnoDB free: 6144 kB; (`group`) REFER `moss/groups`(`name`) ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_new`
--

DROP TABLE IF EXISTS `users_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_new` (
  `unId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY (`unId`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-01-10  2:19:28
