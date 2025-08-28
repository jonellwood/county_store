CREATE TABLE `producttypes` (
  `productType_id` int(11) NOT NULL AUTO_INCREMENT,
  `productType` varchar(45) DEFAULT NULL,
  `isactive` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`productType_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8