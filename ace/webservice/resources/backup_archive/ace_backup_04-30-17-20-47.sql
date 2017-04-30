-- MySQL dump 10.16  Distrib 10.1.13-MariaDB, for Win32 (AMD64)
--
-- Host: localhost    Database: ace
-- ------------------------------------------------------
-- Server version	10.1.13-MariaDB

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
-- Table structure for table `admin_account`
--

DROP TABLE IF EXISTS `admin_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_account` (
  `email` varchar(255) COLLATE utf8_bin NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_account`
--

LOCK TABLES `admin_account` WRITE;
/*!40000 ALTER TABLE `admin_account` DISABLE KEYS */;
INSERT INTO `admin_account` VALUES ('sample@admin1.com',1),('sample@admin2.com',2);
/*!40000 ALTER TABLE `admin_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `admin_view`
--

DROP TABLE IF EXISTS `admin_view`;
/*!50001 DROP VIEW IF EXISTS `admin_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `admin_view` (
  `email` tinyint NOT NULL,
  `user_type_id` tinyint NOT NULL,
  `first_name` tinyint NOT NULL,
  `last_name` tinyint NOT NULL,
  `contact_number` tinyint NOT NULL,
  `hash` tinyint NOT NULL,
  `hashcode` tinyint NOT NULL,
  `status` tinyint NOT NULL,
  `token_exp` tinyint NOT NULL,
  `department_id` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `college_level`
--

DROP TABLE IF EXISTS `college_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `college_level` (
  `college_level_id` int(11) NOT NULL AUTO_INCREMENT,
  `college_level` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`college_level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `college_level`
--

LOCK TABLES `college_level` WRITE;
/*!40000 ALTER TABLE `college_level` DISABLE KEYS */;
INSERT INTO `college_level` VALUES (1,'First Year'),(2,'Second Year'),(3,'Third Year'),(4,'Fourth Year');
/*!40000 ALTER TABLE `college_level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `college_program`
--

DROP TABLE IF EXISTS `college_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `college_program` (
  `program_id` int(11) NOT NULL,
  `program` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `college_program`
--

LOCK TABLES `college_program` WRITE;
/*!40000 ALTER TABLE `college_program` DISABLE KEYS */;
INSERT INTO `college_program` VALUES (1,'Software Engineering'),(2,'Game Development'),(3,'Web Development'),(4,'Animation'),(5,'Multimedia Arts'),(6,'Fashion Design'),(7,'Real Estate Management'),(8,'Business Administration');
/*!40000 ALTER TABLE `college_program` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `college_report`
--

DROP TABLE IF EXISTS `college_report`;
/*!50001 DROP VIEW IF EXISTS `college_report`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `college_report` (
  `report_id` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `faculty_fname` tinyint NOT NULL,
  `faculty_lname` tinyint NOT NULL,
  `student_id` tinyint NOT NULL,
  `department_id` tinyint NOT NULL,
  `report_status_id` tinyint NOT NULL,
  `report_status` tinyint NOT NULL,
  `report_date` tinyint NOT NULL,
  `subject_name` tinyint NOT NULL,
  `term` tinyint NOT NULL,
  `school_year` tinyint NOT NULL,
  `referral_comment` tinyint NOT NULL,
  `counselor_note` tinyint NOT NULL,
  `is_read` tinyint NOT NULL,
  `status` tinyint NOT NULL,
  `program` tinyint NOT NULL,
  `level` tinyint NOT NULL,
  `student_fname` tinyint NOT NULL,
  `student_lname` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `college_student`
--

DROP TABLE IF EXISTS `college_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `college_student` (
  `student_id` varchar(11) COLLATE utf8_bin NOT NULL,
  `program_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `report_count` int(11) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`student_id`),
  CONSTRAINT `collegestudent_studentid_student_studentid` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `college_student`
--

LOCK TABLES `college_student` WRITE;
/*!40000 ALTER TABLE `college_student` DISABLE KEYS */;
/*!40000 ALTER TABLE `college_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `college_view`
--

DROP TABLE IF EXISTS `college_view`;
/*!50001 DROP VIEW IF EXISTS `college_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `college_view` (
  `department_id` tinyint NOT NULL,
  `first_name` tinyint NOT NULL,
  `last_name` tinyint NOT NULL,
  `student_id` tinyint NOT NULL,
  `program_id` tinyint NOT NULL,
  `level_id` tinyint NOT NULL,
  `report_count` tinyint NOT NULL,
  `status` tinyint NOT NULL,
  `program` tinyint NOT NULL,
  `level` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department`
--

LOCK TABLES `department` WRITE;
/*!40000 ALTER TABLE `department` DISABLE KEYS */;
INSERT INTO `department` VALUES (1,'Senior High School'),(2,'College');
/*!40000 ALTER TABLE `department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faculty_account`
--

DROP TABLE IF EXISTS `faculty_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faculty_account` (
  `email` varchar(255) COLLATE utf8_bin NOT NULL,
  `reported_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faculty_account`
--

LOCK TABLES `faculty_account` WRITE;
/*!40000 ALTER TABLE `faculty_account` DISABLE KEYS */;
INSERT INTO `faculty_account` VALUES ('sample@faculty1.com',0),('sample@faculty2.com',0);
/*!40000 ALTER TABLE `faculty_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `faculty_view`
--

DROP TABLE IF EXISTS `faculty_view`;
/*!50001 DROP VIEW IF EXISTS `faculty_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `faculty_view` (
  `email` tinyint NOT NULL,
  `user_type_id` tinyint NOT NULL,
  `first_name` tinyint NOT NULL,
  `last_name` tinyint NOT NULL,
  `contact_number` tinyint NOT NULL,
  `hash` tinyint NOT NULL,
  `hashcode` tinyint NOT NULL,
  `status` tinyint NOT NULL,
  `token_exp` tinyint NOT NULL,
  `reported_count` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) DEFAULT NULL,
  `sender_email` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `receiver_email` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `message_subject` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `message_body` mediumtext COLLATE utf8_bin,
  `message_date` timestamp NULL DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  `is_read_sender` tinyint(1) DEFAULT NULL,
  `receiver_status` tinyint(1) DEFAULT NULL,
  `sender_status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reason`
--

DROP TABLE IF EXISTS `reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reason` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) DEFAULT NULL,
  `referral_reason_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reason`
--

LOCK TABLES `reason` WRITE;
/*!40000 ALTER TABLE `reason` DISABLE KEYS */;
/*!40000 ALTER TABLE `reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `student_id` varchar(11) COLLATE utf8_bin DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `report_status_id` int(11) DEFAULT '1',
  `report_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `subject_name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `term` int(11) DEFAULT NULL,
  `school_year` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `referral_comment` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `counselor_note` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `is_updated` tinyint(1) DEFAULT '0',
  `status` int(11) DEFAULT '1',
  PRIMARY KEY (`report_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `report_studentid_student_studentid` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report`
--

LOCK TABLES `report` WRITE;
/*!40000 ALTER TABLE `report` DISABLE KEYS */;
/*!40000 ALTER TABLE `report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_reason`
--

DROP TABLE IF EXISTS `report_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_reason` (
  `referral_reason_id` int(11) NOT NULL,
  `referral_reason` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`referral_reason_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_reason`
--

LOCK TABLES `report_reason` WRITE;
/*!40000 ALTER TABLE `report_reason` DISABLE KEYS */;
INSERT INTO `report_reason` VALUES (1,'Student is habitually absent or late (reaching half of allowed absences)'),(2,'Student is underachieving (smart but lazy)'),(3,'Student shows inability to perform in class (signs of failing)'),(4,'Student plans to transfer to another class'),(5,'Student shows violent/disruptive behavior'),(6,'Student shows emotional distress');
/*!40000 ALTER TABLE `report_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_status`
--

DROP TABLE IF EXISTS `report_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_status` (
  `report_status_id` int(11) NOT NULL,
  `report_status` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`report_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_status`
--

LOCK TABLES `report_status` WRITE;
/*!40000 ALTER TABLE `report_status` DISABLE KEYS */;
INSERT INTO `report_status` VALUES (1,'Uncounseled'),(2,'In Progress'),(3,'Counseled');
/*!40000 ALTER TABLE `report_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shs_level`
--

DROP TABLE IF EXISTS `shs_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shs_level` (
  `shs_level_id` int(11) NOT NULL AUTO_INCREMENT,
  `shs_level` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`shs_level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shs_level`
--

LOCK TABLES `shs_level` WRITE;
/*!40000 ALTER TABLE `shs_level` DISABLE KEYS */;
INSERT INTO `shs_level` VALUES (1,'Grade 11'),(2,'Grade 12');
/*!40000 ALTER TABLE `shs_level` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shs_program`
--

DROP TABLE IF EXISTS `shs_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shs_program` (
  `program_id` int(11) NOT NULL,
  `program` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shs_program`
--

LOCK TABLES `shs_program` WRITE;
/*!40000 ALTER TABLE `shs_program` DISABLE KEYS */;
INSERT INTO `shs_program` VALUES (1,'Humanities And Social Sciences'),(2,'Accountancy Business And Management'),(3,'Computer Programming '),(4,'Animation'),(5,'Fashion Design'),(6,'Multimedia Arts');
/*!40000 ALTER TABLE `shs_program` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `shs_report`
--

DROP TABLE IF EXISTS `shs_report`;
/*!50001 DROP VIEW IF EXISTS `shs_report`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `shs_report` (
  `report_id` tinyint NOT NULL,
  `email` tinyint NOT NULL,
  `faculty_fname` tinyint NOT NULL,
  `faculty_lname` tinyint NOT NULL,
  `student_id` tinyint NOT NULL,
  `department_id` tinyint NOT NULL,
  `report_status_id` tinyint NOT NULL,
  `report_status` tinyint NOT NULL,
  `report_date` tinyint NOT NULL,
  `subject_name` tinyint NOT NULL,
  `term` tinyint NOT NULL,
  `school_year` tinyint NOT NULL,
  `referral_comment` tinyint NOT NULL,
  `counselor_note` tinyint NOT NULL,
  `is_read` tinyint NOT NULL,
  `status` tinyint NOT NULL,
  `program` tinyint NOT NULL,
  `level` tinyint NOT NULL,
  `student_fname` tinyint NOT NULL,
  `student_lname` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `shs_student`
--

DROP TABLE IF EXISTS `shs_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shs_student` (
  `student_id` varchar(11) COLLATE utf8_bin NOT NULL,
  `program_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `report_count` int(11) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`student_id`),
  CONSTRAINT `shsstudent_studentid_student_studentid` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shs_student`
--

LOCK TABLES `shs_student` WRITE;
/*!40000 ALTER TABLE `shs_student` DISABLE KEYS */;
/*!40000 ALTER TABLE `shs_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `shs_view`
--

DROP TABLE IF EXISTS `shs_view`;
/*!50001 DROP VIEW IF EXISTS `shs_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `shs_view` (
  `department_id` tinyint NOT NULL,
  `first_name` tinyint NOT NULL,
  `last_name` tinyint NOT NULL,
  `student_id` tinyint NOT NULL,
  `program_id` tinyint NOT NULL,
  `level_id` tinyint NOT NULL,
  `report_count` tinyint NOT NULL,
  `status` tinyint NOT NULL,
  `program` tinyint NOT NULL,
  `level` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student` (
  `student_id` varchar(11) COLLATE utf8_bin NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student`
--

LOCK TABLES `student` WRITE;
/*!40000 ALTER TABLE `student` DISABLE KEYS */;
/*!40000 ALTER TABLE `student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `superadmin_account`
--

DROP TABLE IF EXISTS `superadmin_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `superadmin_account` (
  `email` varchar(255) COLLATE utf8_bin NOT NULL,
  `user_type_id` int(11) DEFAULT NULL,
  `hash` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `hashcode` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `token_exp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `superadmin_account`
--

LOCK TABLES `superadmin_account` WRITE;
/*!40000 ALTER TABLE `superadmin_account` DISABLE KEYS */;
INSERT INTO `superadmin_account` VALUES ('super@admin.com',1,'$2y$10$nwQXh6yJmO9hir.TwVIR0OjLGJXTPZrGojXzF0qfLbdiXDb96eK1S',NULL,NULL);
/*!40000 ALTER TABLE `superadmin_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `email` varchar(255) COLLATE utf8_bin NOT NULL,
  `user_type_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `contact_number` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `hash` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `hashcode` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `token_exp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('sample@admin1.com',2,'zxcvb','asdfg','12345','$2y$10$7JGW7ERhNGeQVsV9n4vPs.XgnHY7WE9RE9EAHXP.g9RBBwAcj2Nu2',NULL,1,NULL),('sample@admin2.com',2,'yuiop','hjkl','5678','$2y$10$0Oxo6MiRonBfVJpSKkhhUOu/.qflRqKMUMqTUw5UgOHSAI66qqkY.',NULL,1,NULL),('sample@faculty1.com',3,'fghj','tyui','456789','$2y$10$/QYizEFo.8QOmI7Z1QMGsehG6FmrrjKW0GuCq8od.Zp/lSwcwaiPC',NULL,1,NULL),('sample@faculty2.com',3,'vbnm','tyui','45678','$2y$10$58xXo9Qd3O3BH5R.pOO1nemkPfl1ZjkXyeKse.sXffkXduB0Ttg5G',NULL,1,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_type`
--

DROP TABLE IF EXISTS `user_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_type` (
  `user_type_id` int(11) NOT NULL,
  `user_type` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`user_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_type`
--

LOCK TABLES `user_type` WRITE;
/*!40000 ALTER TABLE `user_type` DISABLE KEYS */;
INSERT INTO `user_type` VALUES (1,'Super Administrator'),(2,'Administrator'),(3,'Faculty Member');
/*!40000 ALTER TABLE `user_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `admin_view`
--

/*!50001 DROP TABLE IF EXISTS `admin_view`*/;
/*!50001 DROP VIEW IF EXISTS `admin_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `admin_view` AS select `user`.`email` AS `email`,`user`.`user_type_id` AS `user_type_id`,`user`.`first_name` AS `first_name`,`user`.`last_name` AS `last_name`,`user`.`contact_number` AS `contact_number`,`user`.`hash` AS `hash`,`user`.`hashcode` AS `hashcode`,`user`.`status` AS `status`,`user`.`token_exp` AS `token_exp`,`admin_account`.`department_id` AS `department_id` from (`user` join `admin_account` on((`user`.`email` = `admin_account`.`email`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `college_report`
--

/*!50001 DROP TABLE IF EXISTS `college_report`*/;
/*!50001 DROP VIEW IF EXISTS `college_report`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `college_report` AS select `a`.`report_id` AS `report_id`,`a`.`email` AS `email`,(select `user`.`first_name` from `user` where (`a`.`email` = `user`.`email`)) AS `faculty_fname`,(select `user`.`last_name` from `user` where (`a`.`email` = `user`.`email`)) AS `faculty_lname`,`a`.`student_id` AS `student_id`,`a`.`department_id` AS `department_id`,`a`.`report_status_id` AS `report_status_id`,(select `report_status`.`report_status` from `report_status` where (`a`.`report_status_id` = `report_status`.`report_status_id`)) AS `report_status`,`a`.`report_date` AS `report_date`,`a`.`subject_name` AS `subject_name`,`a`.`term` AS `term`,`a`.`school_year` AS `school_year`,`a`.`referral_comment` AS `referral_comment`,`a`.`counselor_note` AS `counselor_note`,`a`.`is_read` AS `is_read`,`a`.`status` AS `status`,(select `college_program`.`program` from `college_program` where (`b`.`program_id` = `college_program`.`program_id`)) AS `program`,(select `college_level`.`college_level` from `college_level` where (`b`.`level` = `college_level`.`college_level_id`)) AS `level`,(select `student`.`first_name` from `student` where (`a`.`student_id` = `student`.`student_id`)) AS `student_fname`,(select `student`.`last_name` from `student` where (`a`.`student_id` = `student`.`student_id`)) AS `student_lname` from (`report` `a` left join `college_student` `b` on((`a`.`student_id` = `b`.`student_id`))) where (`a`.`department_id` = 2) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `college_view`
--

/*!50001 DROP TABLE IF EXISTS `college_view`*/;
/*!50001 DROP VIEW IF EXISTS `college_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `college_view` AS select `student`.`department_id` AS `department_id`,`student`.`first_name` AS `first_name`,`student`.`last_name` AS `last_name`,`college_student`.`student_id` AS `student_id`,`college_student`.`program_id` AS `program_id`,`college_student`.`level` AS `level_id`,`college_student`.`report_count` AS `report_count`,`college_student`.`status` AS `status`,`college_program`.`program` AS `program`,`college_level`.`college_level` AS `level` from (((`student` join `college_student` on((`student`.`student_id` = `college_student`.`student_id`))) join `college_program` on((`college_student`.`program_id` = `college_program`.`program_id`))) join `college_level` on((`college_student`.`level` = `college_level`.`college_level_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `faculty_view`
--

/*!50001 DROP TABLE IF EXISTS `faculty_view`*/;
/*!50001 DROP VIEW IF EXISTS `faculty_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `faculty_view` AS select `user`.`email` AS `email`,`user`.`user_type_id` AS `user_type_id`,`user`.`first_name` AS `first_name`,`user`.`last_name` AS `last_name`,`user`.`contact_number` AS `contact_number`,`user`.`hash` AS `hash`,`user`.`hashcode` AS `hashcode`,`user`.`status` AS `status`,`user`.`token_exp` AS `token_exp`,`faculty_account`.`reported_count` AS `reported_count` from (`user` join `faculty_account` on((`user`.`email` = `faculty_account`.`email`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `shs_report`
--

/*!50001 DROP TABLE IF EXISTS `shs_report`*/;
/*!50001 DROP VIEW IF EXISTS `shs_report`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `shs_report` AS select `a`.`report_id` AS `report_id`,`a`.`email` AS `email`,(select `user`.`first_name` from `user` where (`a`.`email` = `user`.`email`)) AS `faculty_fname`,(select `user`.`last_name` from `user` where (`a`.`email` = `user`.`email`)) AS `faculty_lname`,`a`.`student_id` AS `student_id`,`a`.`department_id` AS `department_id`,`a`.`report_status_id` AS `report_status_id`,(select `report_status`.`report_status` from `report_status` where (`a`.`report_status_id` = `report_status`.`report_status_id`)) AS `report_status`,`a`.`report_date` AS `report_date`,`a`.`subject_name` AS `subject_name`,`a`.`term` AS `term`,`a`.`school_year` AS `school_year`,`a`.`referral_comment` AS `referral_comment`,`a`.`counselor_note` AS `counselor_note`,`a`.`is_read` AS `is_read`,`a`.`status` AS `status`,(select `shs_program`.`program` from `shs_program` where (`b`.`program_id` = `shs_program`.`program_id`)) AS `program`,(select `shs_level`.`shs_level` from `shs_level` where (`b`.`level` = `shs_level`.`shs_level_id`)) AS `level`,(select `student`.`first_name` from `student` where (`a`.`student_id` = `student`.`student_id`)) AS `student_fname`,(select `student`.`last_name` from `student` where (`a`.`student_id` = `student`.`student_id`)) AS `student_lname` from (`report` `a` left join `shs_student` `b` on((`a`.`student_id` = `b`.`student_id`))) where (`a`.`department_id` = 1) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `shs_view`
--

/*!50001 DROP TABLE IF EXISTS `shs_view`*/;
/*!50001 DROP VIEW IF EXISTS `shs_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `shs_view` AS select `student`.`department_id` AS `department_id`,`student`.`first_name` AS `first_name`,`student`.`last_name` AS `last_name`,`shs_student`.`student_id` AS `student_id`,`shs_student`.`program_id` AS `program_id`,`shs_student`.`level` AS `level_id`,`shs_student`.`report_count` AS `report_count`,`shs_student`.`status` AS `status`,`shs_program`.`program` AS `program`,`shs_level`.`shs_level` AS `level` from (((`student` join `shs_student` on((`student`.`student_id` = `shs_student`.`student_id`))) join `shs_program` on((`shs_student`.`program_id` = `shs_program`.`program_id`))) join `shs_level` on((`shs_student`.`level` = `shs_level`.`shs_level_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-04-30 20:47:32
