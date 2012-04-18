CREATE TABLE IF NOT EXISTS `cel_content_elements` (
  `element_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `title` date NOT NULL,
  `config` text NOT NULL,
  `sort` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `active` tinyint(4) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`element_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_content_pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_page_id` int(11) NOT NULL,
  `template` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `nav_title` varchar(255) NOT NULL,
  `clean_title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `config` text NOT NULL,
  `sort` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_extensions` (
  `extension_key` varchar(255) NOT NULL,
  `core` tinyint(1) NOT NULL,
  `deps` text NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`extension_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_permissions` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_permissions_x_users` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_photo_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` int(11) NOT NULL,
  `description` text NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_photo_comments` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_photo_exif` (
  `photo_id` int(11) NOT NULL,
  `exif_data` text NOT NULL,
  UNIQUE KEY `photo_id` (`photo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_photo_photos` (
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
  PRIMARY KEY (`photo_id`),
  KEY `tags` (`tags`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_photo_photos_x_category` (
  `photo_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  KEY `photo_id` (`photo_id`,`category_id`),
  KEY `photo_id_2` (`photo_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_usergroups` (
  `usergroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_usergroup_id` int(11) NOT NULL,
  `owner_user_id` int(11) NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`usergroup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_userprops` (
  `prop_id` int(11) NOT NULL AUTO_INCREMENT,
  `prop_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`prop_id`),
  KEY `prop_name` (`prop_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_userprops_x_users` (
  `user_id` int(11) NOT NULL,
  `prop_id` int(11) NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `access_level` tinyint(4) NOT NULL,
  UNIQUE KEY `user_id_2` (`user_id`,`prop_id`),
  KEY `user_id` (`user_id`),
  KEY `prop_id` (`prop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_users` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
-- QUERY END

CREATE TABLE IF NOT EXISTS `cel_users_x_usergroups` (
  `rel_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `usergroup_id` int(11) NOT NULL,
  PRIMARY KEY (`rel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
-- QUERY END

INSERT INTO `cel_userprops` (`prop_id`, `prop_name`) VALUES
(1, 'firstname'),
(2, 'middlename'),
(3, 'lastname'),
(4, 'birthname'),
(5, 'salutation'),
(6, 'title'),
(7, 'gender'),
(8, 'birthday');
-- QUERY END

INSERT INTO `cel_content_pages` (`page_id`, `parent_page_id`, `template`, `title`, `nav_title`, `clean_title`, `content`, `config`, `sort`, `date_created`, `hidden`, `active`) VALUES
(1, 0, '', 'Home', '', 'home', 'This is another Exhibit Blog', '', 0, '0000-00-00 00:00:00', 0, 1);
-- QUERY END

INSERT INTO `cel_extensions` (`extension_key`, `core`, `deps`, `active`) VALUES
('SystemTweaks', 1, '', 1),
('ViewFunctions', 1, '', 1),
('TestVoid', 0, '', 0),
('MobileStyles', 1, '', 1);
-- QUERY END

INSERT INTO `cel_permissions` (`permission_id`, `class`, `method`, `param`, `link`, `title`) VALUES
(1, 'Admin_Controller_Photo', 'edit', 'own', 'Photo/edit', 'Photo → edit → own photos'),
(2, 'Admin_Controller_Photo', 'add', '', 'Photo/add', 'Photo → add new'),
(3, 'Admin_Controller_Photo', 'delete', 'own', 'Photo/delete', 'Photo → delete → own photos'),
(4, 'Admin_Controller_Photo', 'edit', 'other', 'Photo/edit', 'Photo → edit → other''s photos'),
(5, 'Admin_Controller_User', 'edit', 'own', 'User/edit', 'User → edit → own profile'),
(6, 'Admin_Controller_User', 'edit', 'other', 'User/edit', 'User → edit → other''s profiles'),
(7, 'Admin_Controller_User', 'add', '', 'User/add', 'User → add new'),
(8, 'Admin_Controller_User', 'delete', 'own', 'User/delete', 'User → delete → own profile'),
(9, 'Admin_Controller_User', 'delete', 'other', 'User/delete', 'User → delete → other''s profiles'),
(10, 'Admin_Controller_User', 'permissions', 'own', 'User/permissions', 'User → set permissions → own'),
(11, 'Admin_Controller_User', 'permissions', 'other', 'User/permissions', 'User → set permissions → other users'),
(12, 'Admin_Controller_User', 'profile', 'own', 'User/profile', 'User → view → own profile'),
(13, 'Admin_Controller_Comments', 'view', '', 'Comments/view', 'Comments → view'),
(14, 'Admin_Controller_Comments', 'edit', '', 'Comments/edit', 'Comments → edit'),
(15, 'Admin_Controller_Photo', 'view', '', 'Photo/view', 'Photo → overview'),
(16, 'Admin_Controller_Comments', 'delete', '', 'Comments/delete', 'Comments → delete'),
(17, 'Admin_Controller_User', 'view', '', 'User/view', 'User → view list'),
(18, 'Admin_Controller_Settings', 'edit', '', 'Settings/edit', 'Settings → edit'),
(19, 'Admin_Controller_Settings', 'edit', 'system', 'Settings/system', 'Settings → edit → System'),
(20, 'Admin_Controller_Settings', 'edit', 'theme', 'Settings/theme', 'Settings → edit → Theme'),
(21, 'Admin_Controller_Settings', 'edit', 'general', 'Settings/general', 'Settings → edit → General'),
(22, 'Admin_Controller_User', 'settings', 'own', 'User/settings', 'User → settings → own'),
(23, 'Admin_Controller_User', 'settings', 'other', 'User/settings', 'User → settings → other profiles'),
(24, 'Admin_Controller_Extensions', 'manage', '', 'Extensions/manage', 'Extensions → manage'),
(25, 'Admin_Controller_Extensions', 'browse', '', 'Extensions/browse', 'Extensions → browse'),
(26, 'Admin_Controller_Extensions', 'activate', '', 'Extensions/activate', 'Extensions → activate'),
(27, 'Admin_Controller_Extensions', 'deactivate', '', 'Extensions/deactivate', 'Extensions → deactivate'),
(28, 'Admin_Controller_Extensions', 'install', '', 'Extensions/install', 'Extensions → install'),
(29, 'Admin_Controller_Photo', 'delete', 'other', 'Photo/delete', 'Photo → delete → other''s photos'),
(30, 'Admin_Controller_System', 'update', '', 'System/update', 'System → update'),
(31, 'Admin_Controller_System', 'backup', '', 'System/backup', 'System → create backup'),
(32, 'Admin_Controller_System', 'index', '', 'System/index', 'System → overview'),
(33, 'Admin_Controller_Page', 'edit', '', 'Page/edit', 'Page → edit'),
(34, 'Admin_Controller_Page', 'create', '', 'Page/create', 'Page → create new'),
(35, 'Admin_Controller_Page', 'delete', '', 'Page/delete', 'Page → delete'),
(36, 'Admin_Controller_Page', 'view', '', 'Page/view', 'Page → overview');
-- QUERY END

INSERT INTO `cel_permissions_x_users` (`user_id`, `permission_id`, `value`) VALUES
(1, 1, ''),
(1, 2, ''),
(1, 3, ''),
(1, 4, ''),
(1, 5, ''),
(1, 6, ''),
(1, 7, ''),
(1, 8, ''),
(1, 9, ''),
(1, 10, ''),
(1, 11, ''),
(1, 12, ''),
(1, 13, ''),
(1, 14, ''),
(1, 15, ''),
(1, 16, ''),
(1, 17, ''),
(1, 18, ''),
(1, 19, ''),
(1, 20, ''),
(1, 21, ''),
(1, 22, ''),
(1, 23, ''),
(1, 24, ''),
(1, 25, ''),
(1, 26, ''),
(1, 27, ''),
(1, 28, ''),
(1, 29, ''),
(1, 30, ''),
(1, 31, ''),
(1, 32, ''),
(1, 33, ''),
(1, 34, ''),
(1, 35, ''),
(1, 36, '');
-- QUERY END