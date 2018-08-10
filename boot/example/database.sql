# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.6.25)
# Generation Time: 2016-10-13 05:02:33 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table empresas_info
# ------------------------------------------------------------

DROP TABLE IF EXISTS `empresas_info`;

CREATE TABLE `empresas_info` (
  `cnpj` varchar(14) NOT NULL,
  `nome_fantasia` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `razao_social` varchar(150) NOT NULL,
  `cep` varchar(8) NOT NULL,
  `logradouro` varchar(150) NOT NULL,
  `numero` varchar(8) NOT NULL,
  `complemento` varchar(150) NOT NULL,
  `bairro` varchar(150) NOT NULL,
  `cidade` varchar(150) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `telefone` varchar(14) NOT NULL,
  `ativo` tinyint(4) NOT NULL,
  PRIMARY KEY (`cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `empresas_info` WRITE;
/*!40000 ALTER TABLE `empresas_info` DISABLE KEYS */;

INSERT INTO `empresas_info` (`cnpj`, `nome_fantasia`, `email`, `razao_social`, `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `telefone`, `ativo`)
VALUES
	('01534343434311','HIZZO','orlleite@gmail.com','WOD DIGITAL DESENVOLVIMENTO','23232323','Olá mundo asdf','','Complementozinho','Parque Cuiabá','Cuiabá','mt','65242434343',1),
	('12616825000114','','orlleite@gmail.com','','','','','','','','','',1),
	('53276261000103','Teste 2','orlleite@gmail.com','','78095390','Rua I-5, Quadra 58, Casa 14','10','Exemplo 2','Jardim Oliveira','Cuiabá','mt','6522222222',0),
	('65632426000103','Exemplo 2','orlleite@gmail.com','RAZAO DO SITE','78095442','Rua I-5, Quadra 58, Casa 14','20','','Parque do lago','Cuiabá','mt','6581699390',0),
	('67441254000116','Exemplo 2','contato@emodernos.com.br','','78095000','Rua Pinheiral','13','Bloco A20, Apto 3210','Jardim Paulista','Cuiabá','mt','6532335050',1);

/*!40000 ALTER TABLE `empresas_info` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(42) NOT NULL,
  `token` varchar(42) NOT NULL,
  `local_token` varchar(42) NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table users_groups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users_groups`;

CREATE TABLE `users_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `access_list` varchar(255) NOT NULL,
  `access_add` varchar(255) NOT NULL,
  `access_get` varchar(255) NOT NULL,
  `access_create` varchar(255) NOT NULL,
  `access_save` varchar(255) NOT NULL,
  `access_menu` varchar(60) NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `edited_at` datetime NOT NULL,
  `edited_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `users_groups` WRITE;
/*!40000 ALTER TABLE `users_groups` DISABLE KEYS */;

INSERT INTO `users_groups` (`id`, `name`, `access_list`, `access_add`, `access_get`, `access_create`, `access_save`, `access_menu`, `created_at`, `created_by`, `edited_at`, `edited_by`)
VALUES
	(1,'Administrador','#','#','#','#','#','admin','0000-00-00 00:00:00',0,'0000-00-00 00:00:00',0);

/*!40000 ALTER TABLE `users_groups` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table empresas
# ------------------------------------------------------------

DROP TABLE IF EXISTS `empresas`;

CREATE TABLE `empresas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `cnpj` varchar(14) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `saldo` decimal(10,2) NOT NULL,
  `limite_total` decimal(10,2) NOT NULL,
  `limite_usado` decimal(10,2) NOT NULL,
  `acesso` tinyint(4) NOT NULL,
  `grupo` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `edited_by` int(11) DEFAULT NULL,
  `edited_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`cnpj`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;

INSERT INTO `empresas` (`id`, `nome`, `cnpj`, `imagem`, `saldo`, `limite_total`, `limite_usado`, `acesso`, `grupo`, `created_by`, `created_at`, `edited_by`, `edited_at`)
VALUES
	(1,'PRIMEIRO ADMIN','12616825000114',NULL,-419.01,2000.00,419.01,1,1,NULL,NULL,NULL,NULL),
	(2,'WOD DIGITAL DESENVOLVIMENTO','01534343434311',NULL,0.00,0.00,0.00,1,2,NULL,NULL,NULL,NULL),
	(3,'EMPREENDIMENTOS MODERNOS LTDA','67441254000116',NULL,0.00,0.00,0.00,0,2,NULL,NULL,NULL,NULL),
	(6,'RAZAO DO SITE','76224295000141',NULL,0.00,0.00,0.00,0,2,NULL,'2016-02-02 22:23:32',NULL,NULL);

/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
