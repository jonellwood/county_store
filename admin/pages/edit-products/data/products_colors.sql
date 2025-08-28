CREATE TABLE `products_colors` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fkProduct_idx` (`product_id`),
  KEY `fkColors_idx` (`color_id`),
  CONSTRAINT `fkColors` FOREIGN KEY (`color_id`) REFERENCES `colors` (`color_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2405 DEFAULT CHARSET=utf8 COMMENT='products to colors relationship'