CREATE TABLE `products_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product` int(11) NOT NULL,
  `gender_filter` int(11) DEFAULT NULL,
  `type_filter` int(11) DEFAULT NULL,
  `size_filter` int(11) DEFAULT NULL,
  `sleeve_filter` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=431 DEFAULT CHARSET=utf8