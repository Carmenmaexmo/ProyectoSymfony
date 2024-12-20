-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: proyectosymfony
-- ------------------------------------------------------
-- Server version	8.0.36

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `confirmation_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Carmenmaexmo@gmail.com','[\"ROLE_ADMIN\"]','$2y$13$aUly6KatHm/oyjAnXdmzGO6oaBRJifh8tAxCl2HFeAsRQKJDzgdpS',1,NULL),(2,'carmen@gmail.com','[]','$2y$13$ZI61BCQ3THDXR/TDmz/4f.sqhAsuqQxpu1MuQe6g5zGj2hUX7tWf6',1,NULL),(3,'felipe@gmail.com','[]','$2y$13$IHsmoQzWSXyNMjgVyuYJk.eWK70tJMVn8vrbYnXv8Z.U2q8XvCvyG',1,NULL),(5,'carmenextremera3@gmail.com','[]','$2y$13$V.5iO160JPHM81DITuOEw.z.jL/arCAUoquJ92oYz1.BgS8TU.DEO',1,'3858cdd1da124c9c0aa853830cd082881df3c41a435261a65c6883a30f0ad120'),(6,'alejandroperezmillan59@gmail.com','[]','$2y$13$Be/6zqUgLTft0p2fDvdTuOcGKplVyrFK4CoOxq0v7MBGNv9jIAN7K',1,NULL),(7,'davidgamersp@gmail.com','[]','$2y$13$6cDbXgVlDBZzYN2HdZ/rB.k.0sWnjKrZ/zhKgbIkNzxQpJjP6K75G',1,NULL),(21,'correo@gmail.com','[]','$2y$13$TINIsjhwmaZaZYJGtxfM1OEz/viF.uM9bePLyIo1Zh/f1ErL9JI6S',1,NULL),(22,'felipe5@gmail.com','[]','$2y$13$0/S7ymVrIvOaZQz86tds2O6sQiuUkXtGwLTW14NKnL6NpnnTptWxO',1,NULL),(23,'nuevo@gmail.com','[]','$2y$13$1HMA7ZqkjJOMHLjuwt1umuAFkSi6d57Uk2gLgjEtIGJ6Hh3AFdWGK',1,NULL);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-12-20 10:30:21
