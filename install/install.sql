CREATE TABLE `cel_content_elements` (
  `element_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `title` date NOT NULL,
  `config` text NOT NULL,
  `sort` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `active` tinyint(4) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`element_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_content_pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_page_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `nav_title` varchar(255) NOT NULL,
  `config` text NOT NULL,
  `sort` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_extensions` (
  `extension_key` varchar(255) NOT NULL,
  `core` tinyint(1) NOT NULL,
  `deps` text NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`extension_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(255) NOT NULL,
  `method` varchar(255) NOT NULL,
  `param` varchar(255) NOT NULL,
  `link` varchar(128) NOT NULL,
  `title` varchar(128) NOT NULL,
  PRIMARY KEY (`permission_id`),
  KEY `controller` (`class`),
  KEY `param` (`param`),
  KEY `method` (`method`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_permissions_x_usergroups` (
  `usergroup_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_permissions_x_users` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_photo_archive_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_photo_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` int(11) NOT NULL,
  `description` text NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_photo_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `url` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `datetime` datetime NOT NULL,
  `ip` varchar(40) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `photo_id` (`photo_id`),
  KEY `active` (`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_photo_exif` (
  `photo_id` int(11) NOT NULL,
  `exif_data` text NOT NULL,
  UNIQUE KEY `photo_id` (`photo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_photo_photos` (
  `photo_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `clean_title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date_uploaded` datetime NOT NULL,
  `date_publish` datetime NOT NULL,
  `original_name` text NOT NULL,
  `original_width` int(11) NOT NULL,
  `original_height` int(11) NOT NULL,
  `web_name` varchar(255) NOT NULL,
  `thumb_name` varchar(255) NOT NULL,
  `info` text NOT NULL,
  `tags` varchar(255) NOT NULL,
  `exif` tinyint(1) NOT NULL,
  `user_id` int(11) NOT NULL,
  `allow_comments` tinyint(1) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `deleted` tinyint(4) NOT NULL,
  `released` tinyint(4) NOT NULL,
  PRIMARY KEY (`photo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_photo_photos_x_category` (
  `photo_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  KEY `photo_id` (`photo_id`,`category_id`),
  KEY `photo_id_2` (`photo_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE `cel_usergroups` (
  `usergroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_usergroup_id` int(11) NOT NULL,
  `owner_user_id` int(11) NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`usergroup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- QUERY END

CREATE TABLE `cel_userprops` (
  `prop_id` int(11) NOT NULL AUTO_INCREMENT,
  `prop_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`prop_id`),
  KEY `prop_name` (`prop_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- QUERY END

CREATE TABLE `cel_userprops_x_users` (
  `user_id` int(11) NOT NULL,
  `prop_id` int(11) NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `access_level` tinyint(4) NOT NULL,
  UNIQUE KEY `user_id_2` (`user_id`,`prop_id`),
  KEY `user_id` (`user_id`),
  KEY `prop_id` (`prop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- QUERY END

CREATE TABLE `cel_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `loginhash` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `passconf` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `avatar` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime NOT NULL,
  `loggedin` tinyint(1) NOT NULL,
  `date_signup` datetime NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `loggedin` (`loggedin`),
  KEY `active` (`active`),
  KEY `passconf` (`passconf`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- QUERY END

CREATE TABLE `cel_users_x_usergroups` (
  `rel_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `usergroup_id` int(11) NOT NULL,
  PRIMARY KEY (`rel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- QUERY END

INSERT INTO cel_extensions (`extension_key`,`core`,`deps`,`active`) VALUES ('SystemTweaks','1','','1');
-- QUERY END

INSERT INTO cel_extensions (`extension_key`,`core`,`deps`,`active`) VALUES ('ViewFunctions','1','','1');
-- QUERY END

INSERT INTO cel_extensions (`extension_key`,`core`,`deps`,`active`) VALUES ('MobileStyles','1','','1');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('1','Admin_Controller_Photo','edit','own','Photo/edit','Photo → edit → own photos');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('2','Admin_Controller_Photo','add','','Photo/add','Photo → add new');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('3','Admin_Controller_Photo','delete','own','Photo/delete','Photo → delete → own photos');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('4','Admin_Controller_Photo','edit','other','Photo/edit','Photo → edit → other\'s photos');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('5','Admin_Controller_User','edit','own','User/edit','User → edit → own profile');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('6','Admin_Controller_User','edit','other','User/edit','User → edit → other\'s profiles');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('7','Admin_Controller_User','add','','User/add','User → add new');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('8','Admin_Controller_User','delete','own','User/delete','User → delete → own profile');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('9','Admin_Controller_User','delete','other','User/delete','User → delete → other\'s profiles');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('10','Admin_Controller_User','permissions','own','User/permissions','User → set permissions → own');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('11','Admin_Controller_User','permissions','other','User/permissions','User → set permissions → other users');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('12','Admin_Controller_User','profile','own','User/profile','User → view → own profile');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('13','Admin_Controller_Comments','view','','Comments/view','Comments → view');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('14','Admin_Controller_Comments','edit','','Comments/edit','Comments → edit');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('15','Admin_Controller_Photo','view','','Photo/view','Photo → overview');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('16','Admin_Controller_Comments','delete','','Comments/delete','Comments → delete');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('17','Admin_Controller_User','view','','User/view','User → view list');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('18','Admin_Controller_Settings','edit','','Settings/edit','Settings → edit');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('19','Admin_Controller_Settings','edit','system','Settings/system','Settings → edit → System');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('20','Admin_Controller_Settings','edit','theme','Settings/theme','Settings → edit → Theme');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('21','Admin_Controller_Settings','edit','general','Settings/general','Settings → edit → General');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('22','Admin_Controller_User','settings','own','User/settings','User → settings → own');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('23','Admin_Controller_User','settings','other','User/settings','User → settings → other profiles');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('24','Admin_Controller_Extensions','manage','','Extensions/manage','Extensions → manage');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('25','Admin_Controller_Extensions','browse','','Extensions/browse','Extensions → browse');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('26','Admin_Controller_Extensions','activate','','Extensions/activate','Extensions → activate');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('27','Admin_Controller_Extensions','deactivate','','Extensions/deactivate','Extensions → deactivate');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('28','Admin_Controller_Extensions','install','','Extensions/install','Extensions → install');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('29','Admin_Controller_Photo','delete','other','Photo/delete','Photo → delete → other\'s photos');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('30','Admin_Controller_System','update','','System/update','System → update');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('31','Admin_Controller_System','backup','','System/backup','System → create backup');
-- QUERY END

INSERT INTO cel_permissions (`permission_id`,`class`,`method`,`param`,`link`,`title`) VALUES ('32','Admin_Controller_System','index','','System/index','System → overview');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','1','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','2','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','3','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','4','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','5','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','6','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','7','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','8','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','9','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','10','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','11','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','12','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','13','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','14','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','15','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','16','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','17','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','18','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','19','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','20','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','21','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','22','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','23','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','24','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','25','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','26','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','27','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','28','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','29','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','30','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','31','');
-- QUERY END

INSERT INTO cel_permissions_x_users (`user_id`,`permission_id`,`value`) VALUES ('1','32','');
-- QUERY END
