CREATE TABLE `category` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `root` INT(10) UNSIGNED DEFAULT NULL,
  `lft` INT(10) UNSIGNED NOT NULL,
  `rgt` INT(10) UNSIGNED NOT NULL,
  `level` SMALLINT(5) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `root` (`root`),
  KEY `lft` (`lft`),
  KEY `rgt` (`rgt`),
  KEY `level` (`level`)
);