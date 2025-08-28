CREATE TABLE `prices` (
  `price_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `size_id` int(11) DEFAULT NULL,
  `price` float DEFAULT '0',
  `isActive` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`price_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3123 DEFAULT CHARSET=utf8