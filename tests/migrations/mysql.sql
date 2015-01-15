/**
 * MySQL
 */

DROP TABLE IF EXISTS `tree`;

CREATE TABLE `tree` (
  `id`    INT(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `lft`   INT(11)      NOT NULL,
  `rgt`   INT(11)      NOT NULL,
  `depth` INT(11)      NOT NULL,
  `name`  VARCHAR(255) NOT NULL
);

DROP TABLE IF EXISTS `multiple_roots_tree`;

CREATE TABLE `multiple_roots_tree` (
  `id`    INT(11)      NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `tree`  INT(11),
  `lft`   INT(11)      NOT NULL,
  `rgt`   INT(11)      NOT NULL,
  `depth` INT(11)      NOT NULL,
  `name`  VARCHAR(255) NOT NULL
);
