-- MySQL dump 10.16  Distrib 10.1.16-MariaDB, for Win32 (AMD64)
--
-- Host: localhost    Database: ace
-- ------------------------------------------------------
-- Server version	10.1.16-MariaDB

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
  `email` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`email`),
  KEY `department_id` (`department_id`),
  KEY `email` (`email`),
  CONSTRAINT `admin_account_department_fk` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON UPDATE CASCADE,
  CONSTRAINT `admin_account_user_fk` FOREIGN KEY (`email`) REFERENCES `user` (`email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_account`
--

LOCK TABLES `admin_account` WRITE;
/*!40000 ALTER TABLE `admin_account` DISABLE KEYS */;
INSERT INTO `admin_account` VALUES ('sample@admin.com',1);
/*!40000 ALTER TABLE `admin_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `college_program`
--

DROP TABLE IF EXISTS `college_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `college_program` (
  `college_program_id` int(11) NOT NULL AUTO_INCREMENT,
  `college_program` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`college_program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
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
-- Table structure for table `college_student`
--

DROP TABLE IF EXISTS `college_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `college_student` (
  `student_id` int(11) NOT NULL,
  `college_program_id` int(11) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  KEY `student_id` (`student_id`),
  KEY `college_program_id` (`college_program_id`),
  CONSTRAINT `college_student_college_program_fk` FOREIGN KEY (`college_program_id`) REFERENCES `college_program` (`college_program_id`) ON UPDATE CASCADE,
  CONSTRAINT `college_student_student_fk` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `college_student`
--

LOCK TABLES `college_student` WRITE;
/*!40000 ALTER TABLE `college_student` DISABLE KEYS */;
INSERT INTO `college_student` VALUES (1,3,1),(2323231,3,3),(2323232,3,3),(23232322,5,2),(23452612,1,3),(32323222,2,1),(201199999,5,1),(201222222,1,3),(201323334,3,2),(201401109,2,1),(201401125,1,3),(201401126,1,3),(201402225,1,3),(201411111,2,3),(201411125,5,3),(201423223,1,1),(201493522,4,2),(201502225,2,2),(201504425,2,2),(201523451,5,1),(203232211,7,3),(232000988,5,2),(232321145,2,3),(232322222,3,2),(232323232,3,2),(2014323232,3,3),(2122346767,3,3),(2147483647,1,3);
/*!40000 ALTER TABLE `college_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `department` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
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
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) DEFAULT NULL,
  `sender_email` varchar(255) DEFAULT NULL,
  `receiver_email` varchar(255) DEFAULT NULL,
  `message_subject` varchar(255) DEFAULT NULL,
  `message_body` text,
  `message_date` timestamp NULL DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  `receiver_status` tinyint(1) DEFAULT NULL,
  `sender_status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`message_id`),
  KEY `report_id` (`report_id`),
  CONSTRAINT `message_report_fk` FOREIGN KEY (`report_id`) REFERENCES `report` (`report_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
INSERT INTO `message` VALUES (1,2,'sample@admin.com','sample@faculty.com','Subject 2','11You got a new message! You got a new message! You got a new message! You got a new message! You got a new message! You got a new message! You got a new message! You got a new message! <br> a new message! You got a new message! <br> a new message! You got a new message! <br> a new message! You got a new message! <br> a new message! You got a new message! <br>','2016-08-08 19:37:22',1,1,1),(2,11,'sample40@faculty.com','sample@faculty.com','Subject 11','dsad','2017-06-15 16:02:00',1,1,1),(3,1,'sample@admin.com','sample@faculty.com','SUBJECT1','You have a new message','2017-02-27 03:43:36',0,1,1),(4,1,'sample@admin.com','sample@faculty.com','SUBJECT1','You have a new message2','2017-02-27 03:43:36',0,1,1),(5,1,'sample@admin.com','sample@faculty.com','SUBJECT1','You have a new message3','2017-02-27 03:43:36',0,1,1),(6,1,'sample@admin.com','sample@faculty.com','SUBJECT1','You have a new message4','2017-02-27 03:43:36',0,1,1),(7,1,'sample@faculty.com','sample@admin.com','Egg','You have a new message. Please kill me now.','2017-02-27 03:44:36',0,1,1),(8,1,'sample@faculty.com','sample@admin.com','Egg','You have a new message. Please kill me now.','2017-02-27 03:44:36',0,1,1),(9,1,'sample@faculty.com','sample@admin.com','Egg','You have a new message. Please kill me now.','2017-02-27 03:44:36',0,1,1),(10,1,'sample@faculty.com','sample@admin.com','Egg','You have a new message. Please kill me now.','2017-02-27 03:44:36',0,1,1),(11,1,'bartido.coleen@gmail.com','sample@faculty.com','Bangtan Sonyeondan','MAY MAY MAY MAY MAY','2017-02-27 03:46:53',0,1,1),(12,1,'sample@faculty.com','bartido.coleen@gmail.com','Bangtan Sonyeondan','Okay woman.','2017-02-27 03:46:53',1,1,1),(13,1,'bartido.coleen@gmail.com','sample@faculty.com','Bangtan Sonyeondan','MAY MAY MAY MAY MAY','2017-02-27 03:46:54',0,1,1),(14,1,'sample@faculty.com','bartido.coleen@gmail.com','Bangtan Sonyeondan','Ipon bes.','2017-02-27 03:46:54',1,1,1),(15,1,'bartido.coleen@gmail.com','sample@faculty.com','Bangtan Sonyeondan','MAY MAY MAY MAY MAY','2017-02-27 03:46:54',0,1,1),(16,12,'sample@faculty.com','bartido.coleen@gmail.com','Bangtan Sonyeondan','Okay woman.','2017-02-27 03:48:11',1,1,1),(17,12,'bartido.coleen@gmail.com','sample@faculty.com','Bangtan Sonyeondan','Okay woman.','2017-02-27 03:48:11',0,1,1),(18,12,'sample@faculty.com','bartido.coleen@gmail.com','Bangtan Sonyeondan','Okay woman.','2017-02-27 03:48:11',1,1,1),(19,12,'bartido.coleen@gmail.com','sample@faculty.com','Bangtan Sonyeondan','I DON\'T kNOW','2017-02-27 03:48:11',0,1,1),(20,12,'sample@faculty.com','bartido.coleen@gmail.com','Bangtan Sonyeondan','Okay woman.','2017-02-27 03:48:11',1,1,1),(21,1,'bartido.coleen@gmail.com','sample@faculty.com','Bangtan Sonyeondan','coleen me maybe','2017-02-28 03:48:40',0,1,1);
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_notification`
--

DROP TABLE IF EXISTS `message_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_notification` (
  `notification_id` int(11) DEFAULT NULL,
  `message_id` int(11) DEFAULT NULL,
  KEY `notification_id` (`notification_id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `message_notification_message_fk` FOREIGN KEY (`message_id`) REFERENCES `message` (`message_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `message_notification_notification_fk` FOREIGN KEY (`notification_id`) REFERENCES `notification` (`notification_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_notification`
--

LOCK TABLES `message_notification` WRITE;
/*!40000 ALTER TABLE `message_notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_type_id` int(11) DEFAULT NULL,
  `sender_email` varchar(255) DEFAULT NULL,
  `receiver_email` varchar(255) DEFAULT NULL,
  `notification_date` timestamp NULL DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `notification_type_id` (`notification_type_id`),
  CONSTRAINT `notification_type_notification_fk` FOREIGN KEY (`notification_type_id`) REFERENCES `notification_type` (`notification_type_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification`
--

LOCK TABLES `notification` WRITE;
/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
INSERT INTO `notification` VALUES (1,1,'sample@admin.com','sample@faculty.com','2017-02-09 16:00:00',0),(4,3,'sample@admin.com','sample@faculty.com','2017-02-12 16:00:00',0);
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_type`
--

DROP TABLE IF EXISTS `notification_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_type` (
  `notification_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`notification_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_type`
--

LOCK TABLES `notification_type` WRITE;
/*!40000 ALTER TABLE `notification_type` DISABLE KEYS */;
INSERT INTO `notification_type` VALUES (1,'New Message'),(2,'New Report'),(3,'Report Update');
/*!40000 ALTER TABLE `notification_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referral_reason`
--

DROP TABLE IF EXISTS `referral_reason`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_reason` (
  `referral_reason_id` int(11) NOT NULL AUTO_INCREMENT,
  `referral_reason` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`referral_reason_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referral_reason`
--

LOCK TABLES `referral_reason` WRITE;
/*!40000 ALTER TABLE `referral_reason` DISABLE KEYS */;
INSERT INTO `referral_reason` VALUES (1,'Student is habitually absent or late (reaching half of allowed absences)'),(2,'Student is underachieving (smart but lazy)'),(3,'Student shows inability to perform in class (signs of failing)'),(4,'Student plans to transfer to another class'),(5,'Student shows violent/disruptive behavior'),(6,'Student shows emotional distress');
/*!40000 ALTER TABLE `referral_reason` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `report_status_id` int(11) DEFAULT NULL,
  `referral_reasons` varchar(256) DEFAULT NULL,
  `report_date` timestamp NULL DEFAULT NULL,
  `subject_name` varchar(255) DEFAULT NULL,
  `term` int(11) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `referral_comment` varchar(255) DEFAULT NULL,
  `counselor_note` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT NULL,
  `is_updated` tinyint(1) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`report_id`),
  KEY `email` (`email`),
  KEY `student_id` (`student_id`),
  KEY `department_id` (`department_id`),
  KEY `report_status_id` (`report_status_id`),
  KEY `referral_reason_id` (`referral_reasons`),
  CONSTRAINT `report_department_fk` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON UPDATE CASCADE,
  CONSTRAINT `report_report_status_fk` FOREIGN KEY (`report_status_id`) REFERENCES `report_status` (`report_status_id`) ON UPDATE CASCADE,
  CONSTRAINT `report_student_fk` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON UPDATE CASCADE,
  CONSTRAINT `report_user_fk` FOREIGN KEY (`email`) REFERENCES `user` (`email`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report`
--

LOCK TABLES `report` WRITE;
/*!40000 ALTER TABLE `report` DISABLE KEYS */;
INSERT INTO `report` VALUES (1,'bartido.coleen@gmail.com',201401125,2,1,NULL,'2016-12-03 16:52:20','JAVA',NULL,'1',NULL,NULL,0,0,1),(2,NULL,201401125,2,1,NULL,'2016-12-03 16:54:09','Math',NULL,'2',NULL,NULL,0,0,1),(3,NULL,201401126,2,1,NULL,'2016-12-03 16:54:50','Rap',NULL,'1',NULL,NULL,0,0,1),(4,NULL,201401129,1,1,NULL,'2016-12-03 16:55:21','Cutie Pie',NULL,'1',NULL,NULL,0,0,1),(5,NULL,201401129,1,1,NULL,'2016-12-03 17:00:51','Cutie Pie',NULL,'1',NULL,NULL,0,0,1),(6,NULL,201401127,1,1,NULL,'2016-12-03 17:01:23','Cutie Pie',NULL,'1',NULL,NULL,0,0,1),(7,NULL,201401109,2,1,NULL,'2016-12-03 17:01:37','Cutie Pie',NULL,'1',NULL,NULL,0,0,1),(10,NULL,2014011111,1,1,NULL,'2016-12-03 17:09:16','report1',NULL,'1',NULL,NULL,0,0,1),(11,NULL,2012333,1,1,NULL,'2016-12-03 17:09:21','report1',NULL,'1',NULL,NULL,0,0,1),(12,NULL,2012333,1,1,NULL,'2016-12-03 17:09:24','report1',NULL,'1',NULL,NULL,0,NULL,1),(13,NULL,23452612,2,1,NULL,'2016-12-03 17:10:58','ewan',NULL,'2',NULL,NULL,0,NULL,1),(14,NULL,2147483647,2,1,NULL,'2016-12-03 17:11:09','ewan',NULL,'2',NULL,NULL,0,NULL,1),(15,NULL,121222,1,1,NULL,'2016-12-03 17:11:59','Coleen',NULL,'2',NULL,NULL,0,NULL,1),(16,NULL,23456789,1,1,NULL,'2016-12-04 05:00:43','fork',NULL,'1',NULL,NULL,0,NULL,1),(18,NULL,12121,1,1,NULL,'2016-12-04 16:16:35','mouhi',NULL,'1',NULL,NULL,0,NULL,1),(20,NULL,201401126,1,1,NULL,'2016-12-04 16:36:37','vgbhkl',NULL,'2',NULL,NULL,0,NULL,1),(21,'sample@faculty.com',201401126,1,1,NULL,'2016-12-04 16:40:16','Java',1,'2',NULL,NULL,0,0,1),(23,'sample@faculty.com',201401126,1,1,NULL,'2016-12-04 16:42:36','PHP',2,'2',NULL,NULL,0,1,1),(24,NULL,56,1,1,'1','2016-12-04 16:45:57','1222',NULL,'2',NULL,NULL,0,NULL,1),(25,NULL,4,1,1,NULL,'2016-12-05 06:35:39','j',NULL,'1',NULL,NULL,0,NULL,1),(26,NULL,4,1,1,NULL,'2016-12-05 06:35:57','j',NULL,'1',NULL,NULL,0,NULL,1),(27,NULL,4,1,1,NULL,'2016-12-05 06:36:18','j',NULL,'1',NULL,NULL,0,NULL,1),(28,NULL,4,1,1,NULL,'2016-12-05 06:37:19','j',NULL,'1',NULL,NULL,0,NULL,1),(29,NULL,4,1,1,NULL,'2016-12-05 06:38:18','j',NULL,'1',NULL,NULL,0,NULL,1),(30,NULL,4,1,1,NULL,'2016-12-05 06:38:31','j',NULL,'1',NULL,NULL,0,NULL,1),(31,NULL,201402225,2,1,NULL,'2016-12-12 23:57:14','THESIS',NULL,'2',NULL,NULL,0,NULL,1),(32,NULL,201402225,2,1,NULL,'2016-12-13 00:05:16','THESIS',2,NULL,NULL,NULL,0,NULL,1),(35,'sample@faculty.com',201502225,2,1,NULL,'2016-12-13 00:10:29','sadla',3,NULL,NULL,NULL,0,NULL,1),(38,'sample@faculty.com',201523451,2,1,NULL,'2016-12-13 00:14:09','any',2,NULL,NULL,NULL,0,NULL,1),(40,'sample@faculty.com',201199999,2,1,NULL,'2016-12-13 00:16:47','any',2,NULL,NULL,NULL,0,NULL,1),(42,'sample@faculty.com',201423223,2,1,NULL,'2016-12-13 00:27:34','JAVA',2,NULL,NULL,NULL,0,NULL,1),(45,'sample@faculty.com',201493522,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student is underachieving (smart but lazy)','2016-12-13 01:18:41','qwqweq',3,NULL,'sadasd',NULL,0,NULL,1),(46,'sample@faculty.com',203232211,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student shows inability to perform in class (signs of failing)','2016-12-13 01:20:27','dasda',2,NULL,NULL,NULL,0,NULL,1),(47,'sample@faculty.com',203232211,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student shows inability to perform in class (signs of failing)','2016-12-13 01:21:46','dasda',2,NULL,'',NULL,0,NULL,1),(48,'sample@faculty.com',232321145,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student shows inability to perform in class (signs of failing),Student plans to transfer to another class','2016-12-13 01:22:14','eqeq',1,NULL,'',NULL,0,NULL,1),(49,'sample@faculty.com',232321145,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student shows inability to perform in class (signs of failing),Student plans to transfer to another class','2016-12-13 01:22:46','eqeq',1,NULL,NULL,NULL,0,NULL,1),(50,'sample@faculty.com',232321145,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student shows inability to perform in class (signs of failing),Student plans to transfer to another class','2016-12-13 01:23:09','eqeq',1,NULL,'',NULL,0,NULL,1),(51,'sample@faculty.com',232321145,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student shows inability to perform in class (signs of failing),Student plans to transfer to another class','2016-12-13 01:23:23','eqeq',1,NULL,'',NULL,0,NULL,1),(52,'sample@faculty.com',232321145,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student shows inability to perform in class (signs of failing),Student plans to transfer to another class','2016-12-13 01:23:30','eqeq',1,NULL,NULL,NULL,0,NULL,1),(53,'sample@faculty.com',232321145,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student shows inability to perform in class (signs of failing),Student plans to transfer to another class','2016-12-13 01:25:00','eqeq',1,NULL,'',NULL,0,NULL,1),(54,'sample@faculty.com',232000988,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student is underachieving (smart but lazy),Student shows inability to perform in class (signs of failing)','2016-12-13 01:25:22','qwewq',2,NULL,'',NULL,0,NULL,1),(55,'sample@faculty.com',232000988,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student is underachieving (smart but lazy),Student shows inability to perform in class (signs of failing)','2016-12-13 01:26:40','qwewq',2,NULL,NULL,NULL,0,NULL,1),(56,NULL,201423644,1,1,NULL,'2016-12-14 07:25:56','dadas',1,NULL,NULL,NULL,0,NULL,1),(57,'sample@faculty.com',201323334,2,1,'Student shows violent/disruptive behavior,Student shows emotional distress','2016-12-20 01:06:12','Java',2,NULL,NULL,NULL,0,NULL,1),(58,'sample@faculty.com',2014323232,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student is underachieving (smart but lazy)','2016-12-20 02:31:00','dasdsa',3,NULL,'',NULL,0,NULL,1),(59,'sample@faculty.com',2323231,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student is underachieving (smart but lazy)','2016-12-20 02:36:29','dadsadsa',3,NULL,NULL,NULL,0,NULL,1),(60,'sample@faculty.com',2323232,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student is underachieving (smart but lazy)','2016-12-20 02:42:42','dsadsa',3,NULL,'dsadasdsa',NULL,0,NULL,1),(61,'sample@faculty.com',2147483647,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student shows inability to perform in class (signs of failing)','2016-12-20 02:45:55','dasdsada',3,'{{currentYe','dadadsa',NULL,0,NULL,1),(62,'sample@faculty.com',2147483647,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student shows inability to perform in class (signs of failing)','2016-12-20 02:47:11','dasdsada',3,'{{currentYear +','dadadsa',NULL,0,NULL,1),(63,'sample@faculty.com',232322222,2,1,'Student is underachieving (smart but lazy),Student shows inability to perform in class (signs of failing)','2016-12-20 02:48:30','dadsadadas',3,'{{(currentYear + 1) ','dsd',NULL,0,NULL,1),(64,'sample@faculty.com',2122346767,2,1,'Student is habitually absent or late (reaching half of allowed absences),Student plans to transfer to another class','2016-12-20 02:50:39','dsadsada',2,'2016 - 2017','dadasd',NULL,0,NULL,1);
/*!40000 ALTER TABLE `report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_notification`
--

DROP TABLE IF EXISTS `report_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_notification` (
  `notification_id` int(11) DEFAULT NULL,
  `report_id` int(11) DEFAULT NULL,
  KEY `notification_id` (`notification_id`),
  KEY `report_id` (`report_id`),
  CONSTRAINT `report_notification_report_fk` FOREIGN KEY (`report_id`) REFERENCES `report` (`report_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_notification`
--

LOCK TABLES `report_notification` WRITE;
/*!40000 ALTER TABLE `report_notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_status`
--

DROP TABLE IF EXISTS `report_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_status` (
  `report_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`report_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
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
-- Table structure for table `shs_program`
--

DROP TABLE IF EXISTS `shs_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shs_program` (
  `shs_program_id` int(11) NOT NULL AUTO_INCREMENT,
  `shs_program` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`shs_program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shs_program`
--

LOCK TABLES `shs_program` WRITE;
/*!40000 ALTER TABLE `shs_program` DISABLE KEYS */;
INSERT INTO `shs_program` VALUES (11,'Humanities And Social Sciences'),(12,'Accountancy Business And Management'),(13,'Computer Programming '),(14,'Animation'),(15,'Fashion Design'),(16,'Multimedia Arts');
/*!40000 ALTER TABLE `shs_program` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shs_student`
--

DROP TABLE IF EXISTS `shs_student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shs_student` (
  `student_id` int(11) NOT NULL,
  `shs_program_id` int(11) DEFAULT NULL,
  `grade_level` int(11) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  KEY `student_id` (`student_id`),
  KEY `shs_program_id` (`shs_program_id`),
  CONSTRAINT `shs_student_shs_program_fk` FOREIGN KEY (`shs_program_id`) REFERENCES `shs_program` (`shs_program_id`) ON UPDATE CASCADE,
  CONSTRAINT `shs_student_student_fk` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shs_student`
--

LOCK TABLES `shs_student` WRITE;
/*!40000 ALTER TABLE `shs_student` DISABLE KEYS */;
INSERT INTO `shs_student` VALUES (4,11,11),(56,11,12),(233,12,11),(12121,11,11),(56789,11,11),(23232323,14,11),(23456789,12,11),(201423644,13,12),(201432323,15,12);
/*!40000 ALTER TABLE `shs_student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`student_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `student_department_fk` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student`
--

LOCK TABLES `student` WRITE;
/*!40000 ALTER TABLE `student` DISABLE KEYS */;
INSERT INTO `student` VALUES (1,2,'c','b',1),(4,1,'j','h',1),(56,1,'b','c',1),(233,1,'Jimin','Park',1),(12121,1,'minyoung','park',1),(56789,1,'jk','ghkjk1',1),(121222,1,'gyujj','SHS1',1),(2012333,1,'asvhas','aagvhj',1),(2323231,2,'dasdsa','dsadsa',1),(2323232,2,'dasdasadsa','dadsadsa',1),(23232322,2,'dad','dsadasa',1),(23232323,1,'asdasa','adas',1),(23452612,2,'wE','PEste',1),(23456789,1,'gooo','Bartido',1),(32323222,2,'qweqeq','dsa',1),(201199999,2,'dqwe','dsad',1),(201222222,2,'Paul','Jao',1),(201323334,2,'First','Last',1),(201401109,2,'jkgvi','vfyguhijkl',1),(201401125,2,'Coleen','Bartido',1),(201401126,2,'Yoon Gi','Min',1),(201401127,1,'jkgvi','vfyguhijkl',1),(201401129,1,'Seok Jin','Kim',1),(201402225,2,'Paul','Jao',1),(201411111,2,'dadas','dsadasd',1),(201411125,2,'qweqweq','dada',1),(201423223,2,'P','J',1),(201423644,1,'qewqeq','dadas',1),(201432323,1,'qweqw','sadas',1),(201493522,2,'dadasad','dsadsad',1),(201502225,2,'dsadsa','dasd',1),(201504425,2,'dsadsa','dasd',1),(201523451,2,'dqwe','dsad',1),(203232211,2,'dasdsad','dasda',1),(232000988,2,'dsad','dad',1),(232321145,2,'ewqeqw','qweqeq',1),(232322222,2,'asdadasd','dadasdas',1),(232323232,2,'dsad','dsada',1),(2014011111,1,'asvhas','aagvhj',1),(2014323232,2,'dsadsa','dsadsa',1),(2122346767,2,'ddadsad','dadsad',1),(2147483647,2,'wE','PEste',1);
/*!40000 ALTER TABLE `student` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `superadmin_account`
--

DROP TABLE IF EXISTS `superadmin_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `superadmin_account` (
  `email` varchar(255) NOT NULL,
  `user_type_id` int(11) DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`email`),
  KEY `user_type_id` (`user_type_id`),
  CONSTRAINT `superadmin_account_user_type_fk` FOREIGN KEY (`user_type_id`) REFERENCES `user_type` (`user_type_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `superadmin_account`
--

LOCK TABLES `superadmin_account` WRITE;
/*!40000 ALTER TABLE `superadmin_account` DISABLE KEYS */;
INSERT INTO `superadmin_account` VALUES ('super@admin.com',1,'superadmin');
/*!40000 ALTER TABLE `superadmin_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `email` varchar(255) NOT NULL,
  `user_type_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `hashcode` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `token_exp` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`),
  KEY `user_type_id` (`user_type_id`),
  CONSTRAINT `user_user_type_fk` FOREIGN KEY (`user_type_id`) REFERENCES `user_type` (`user_type_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('asda@ffg.com',3,'uyudasad','jkk',NULL,NULL,NULL,0,NULL),('bartido.coleen@gmail.com',2,'coleen','bartido','4567890','coleenadmin1','8f9cc847f344358fd0aa8e8b056c518c588f03ccde40f6679336c6699843e3f9',1,'2017-02-28 12:05:50'),('bartido_coleen@yahoo.com.ph',3,'coleen','bartido','12345',NULL,NULL,1,'2016-11-22 04:28:48'),('daddsadas@dgmail.com',2,'awe','dad',NULL,NULL,'8569901b88322991e16aa9a21e9cbb0efeff7cbc9fbd13f7202b8bd2380b4e29',0,'2016-11-28 08:47:31'),('dadsa@dsad',2,'jhda','iuijk',NULL,NULL,NULL,0,NULL),('dasda@sad',3,'dasda','dsada',NULL,NULL,NULL,0,NULL),('dsadsa@dsadas',2,'dsada','dsadsa',NULL,NULL,NULL,0,NULL),('ppj_ppj1@yahoo.com',3,'p','j',NULL,NULL,'e3c42079da70ccb823e5f46f0b300dafb0c18ecfefdcc0c482e2c0fc35a76269',0,NULL),('ppj_ppj2@yahoo.com',2,'P','J',NULL,'Troll','fb48184ea8fda28487aaca2765b931696d99f3db2bf11a29290d7aa0cfcabe6d',1,'2017-02-19 07:24:18'),('ppj_ppj3232@yahoo.com',2,'dasd','yuweh','74783','pass',NULL,1,NULL),('ppj_ppj3@yahoo.com',3,'dfghjkl','vbnm,',NULL,NULL,NULL,0,NULL),('ppj_ppj99@yahoo.com',2,'dsa','dsadas',NULL,NULL,'06e7a81d3dec16bab82268064b46f1e32f194facedd5b062151756346f698b3c',0,NULL),('sadasd@hdasjdjhad',2,'juyuyuuy','uyuyuyuuy',NULL,NULL,NULL,0,NULL),('sample10@admin.com',2,'xsadgfdgdfg','rewfdfsd',NULL,NULL,NULL,0,NULL),('sample111@admin.com',2,'Paul','Jao',NULL,'admin',NULL,0,NULL),('sample111@gmail.com',2,'jsd','dakdjas',NULL,NULL,NULL,0,NULL),('sample232@sad.com',2,'iu','nhj',NULL,NULL,NULL,0,NULL),('sample299@admin.com',2,'iuj','iik',NULL,NULL,NULL,0,NULL),('sample2@faculty.com',3,'x','D','2323253535','faculty2',NULL,1,NULL),('sample40@faculty.com',3,'Juan','dela Cruz',NULL,NULL,NULL,0,NULL),('sample42@faculty.com',3,'dssdg','wehg',NULL,'faculty',NULL,0,NULL),('sample4352@gmail.com',2,'pol','how',NULL,NULL,'3a29a3b463c21a28a44519484e272aa3655b8cf4a274e68933561fb8857f3eb1',0,NULL),('sample5@faculty.com',3,'uyrwyeru','euwruwe',NULL,NULL,NULL,0,NULL),('sample999@admin.com',2,'oi','kjk',NULL,NULL,NULL,0,NULL),('sample999@gmail.com',2,'sadla','sad',NULL,NULL,'66f19c56a14ba963a2c21f7556ab74a56a6de56c992c6b8f6900a878e0649aa0',0,NULL),('sample99@admin.com',2,'yuiop','hjkl;',NULL,NULL,NULL,0,NULL),('sample99@faculty.com',3,'qwertyu','zxcvbnm',NULL,NULL,NULL,0,NULL),('sample@admin.com',2,'Sample','Admin','12345','admin','12345abcde',1,NULL),('sample@faculty.com',3,'Sample','Faculty','343434545454','faculty1',NULL,1,NULL),('sample@zxgdsa.com',2,'uwe','jhj',NULL,NULL,NULL,0,NULL),('vjosiahedric@gmail.com',3,'hjahasja','ieuwqiewq',NULL,NULL,'30f1f089054ef5126e9b914159918cc7e3fcb05c0ccee42c7fcfa401098c7f17',0,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_type`
--

DROP TABLE IF EXISTS `user_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_type` (
  `user_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`user_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_type`
--

LOCK TABLES `user_type` WRITE;
/*!40000 ALTER TABLE `user_type` DISABLE KEYS */;
INSERT INTO `user_type` VALUES (1,'Super Administrator'),(2,'Administrator'),(3,'Faculty Member');
/*!40000 ALTER TABLE `user_type` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-27 20:57:59
