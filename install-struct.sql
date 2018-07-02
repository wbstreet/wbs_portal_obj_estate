DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_portal_obj_estate_apartment`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_portal_obj_estate_apartment` (
  `obj_id` int(11) NOT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `settlement_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `description` text NOT NULL,
  `floor` int(11) NOT NULL DEFAULT '0',
  `floor_total` int(11) NOT NULL DEFAULT '0',
  `square` int(11) NOT NULL DEFAULT '0',
  `land_square` int(11) NOT NULL DEFAULT '0',
  `lat` varchar(50) DEFAULT NULL,
  `lng` varchar(50) DEFAULT NULL,
  `rooms` int(11) NOT NULL DEFAULT '0',
  `address` varchar(255) NOT NULL DEFAULT '',
  `external_id` varchar(255),
  `external_url` varchar(255),
   PRIMARY KEY (`obj_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_portal_obj_estate_category`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_portal_obj_estate_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`category_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_portal_obj_estate_partner`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_portal_obj_estate_partner` (
  `partner_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `partner_name` varchar(255) NOT NULL,
  `partner_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`partner_id`)
){TABLE_ENGINE=MyISAM};

DROP TABLE IF EXISTS `{TABLE_PREFIX}mod_wbs_portal_obj_estate_image`;
CREATE TABLE `{TABLE_PREFIX}mod_wbs_portal_obj_estate_image` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `obj_id` int(11) NOT NULL,
  `image_storage_id` int(11) NOT NULL,
  -- `image_name` varchar(100) NOT NULL,
  -- `image_origin_name` varchar(255) NOT NULL,
  `is_main` int(11) NOT NULL DEFAULT '0',
  `is_active` int(11) NOT NULL DEFAULT '1',
  FOREIGN KEY (image_storage_id) REFERENCES {TABLE_PREFIX}mod_wbs_core_img(img_id)
      ON UPDATE CASCADE
      ON DELETE RESTRICT,
  PRIMARY KEY (`image_id`)
){TABLE_ENGINE=MyISAM};