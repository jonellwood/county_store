CREATE TABLE `colors` (
  `color_id` int(11) NOT NULL AUTO_INCREMENT,
  `color` varchar(45) DEFAULT NULL,
  `p_hex` varchar(10) DEFAULT NULL,
  `s_hex` varchar(10) DEFAULT NULL,
  `t_hex` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`color_id`),
  UNIQUE KEY `id_UNIQUE` (`color_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1465 DEFAULT CHARSET=utf8