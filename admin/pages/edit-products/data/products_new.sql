CREATE TABLE `products_new` (
  `product_id` int(8) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `keep` tinyint(4) DEFAULT '1',
  `product_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `id_UNIQUE` (`product_id`),
  KEY `product_code` (`code`),
  KEY `product_id` (`product_id`),
  FULLTEXT KEY `Full Text` (`code`,`name`,`description`)
) ENGINE=InnoDB AUTO_INCREMENT=461 DEFAULT CHARSET=utf8 COMMENT='Table to house products available from vendor	'