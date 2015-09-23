CREATE TABLE IF NOT EXISTS `@{prefix}@admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(120) NOT NULL,
  `password` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `@{prefix}@category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `@{prefix}@category` (`id`, `name`, `description`, `order`) VALUES
(1, 'همراه اول', 'کارت شارژ همراه اول', 1),
(2, 'ایرانسل', 'کارت شارژ ایرانسل', 2),
(3, 'رایتل', 'کارت شارژ رایتل', 3);

CREATE TABLE IF NOT EXISTS `@{prefix}@field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productid` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `type` varchar(256) NOT NULL,
  `default` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_field_product1_idx` (`productid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `@{prefix}@input` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `type` varchar(120) NOT NULL DEFAULT 'text',
  `order` int(11) NOT NULL DEFAULT '0',
  `data` text,
  `productid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_input_product1_idx` (`productid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

INSERT INTO `@{prefix}@input` ( `name`, `type`, `order`, `data`) VALUES
( 'ایمیل', 'email', 1, 'a:0:{}'),
('شماره تماس', 'mobile', 2, 'a:0:{}');


CREATE TABLE IF NOT EXISTS `@{prefix}@item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productid` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  `paymentid` int(11) DEFAULT NULL,
  `reservetime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_card_product1_idx` (`productid`),
  KEY `fk_card_payment1_idx` (`paymentid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `@{prefix}@option` (
  `key` varchar(120) NOT NULL,
  `category` varchar(120) NOT NULL,
  `value` varchar(120) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`key`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `@{prefix}@payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` int(11) NOT NULL,
  `requesttime` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `clientip` char(15) NOT NULL,
  `paymenttime` int(10) unsigned DEFAULT NULL,
  `reference` varchar(120) DEFAULT NULL,
  `gatewayid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `@{prefix}@payment_meta` (
  `paymentid` int(11) NOT NULL,
  `inputid` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`paymentid`,`inputid`),
  KEY `fk_input_payment1_idx` (`paymentid`),
  KEY `fk_payment_meta_input1_idx` (`inputid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `@{prefix}@plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `class` varchar(120) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_UNIQUE` (`class`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `@{prefix}@product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `price` int(11) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `skipitem` int(11) NOT NULL,
  `categoryid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_category_idx` (`categoryid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `@{prefix}@value` (
  `fieldid` int(11) NOT NULL,
  `itemid` int(11) NOT NULL,
  `value` varchar(256) NOT NULL,
  PRIMARY KEY (`fieldid`,`itemid`),
  KEY `fk_value_field1_idx` (`fieldid`),
  KEY `fk_value_card1_idx` (`itemid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `@{prefix}@field`
  ADD CONSTRAINT `fk_field_product1` FOREIGN KEY (`productid`) REFERENCES `@{prefix}@product` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `@{prefix}@input`
  ADD CONSTRAINT `fk_input_product1` FOREIGN KEY (`productid`) REFERENCES `@{prefix}@product` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `@{prefix}@item`
  ADD CONSTRAINT `fk_card_payment1` FOREIGN KEY (`paymentid`) REFERENCES `@{prefix}@payment` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_card_product1` FOREIGN KEY (`productid`) REFERENCES `@{prefix}@product` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `@{prefix}@payment_meta`
  ADD CONSTRAINT `fk_input_payment1` FOREIGN KEY (`paymentid`) REFERENCES `@{prefix}@payment` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_payment_meta_input1` FOREIGN KEY (`inputid`) REFERENCES `@{prefix}@input` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `@{prefix}@product`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`categoryid`) REFERENCES `@{prefix}@category` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

ALTER TABLE `@{prefix}@value`
  ADD CONSTRAINT `fk_value_card1` FOREIGN KEY (`itemid`) REFERENCES `@{prefix}@item` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_value_field1` FOREIGN KEY (`fieldid`) REFERENCES `@{prefix}@field` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

