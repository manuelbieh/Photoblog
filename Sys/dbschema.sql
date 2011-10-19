-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: dd12628
-- Erstellungszeit: 19. Okt 2011 um 15:00
-- Server Version: 5.1.43
-- PHP-Version: 5.2.12-nmm2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `d0047892`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_content_elements`
--

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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_content_pages`
--

CREATE TABLE IF NOT EXISTS `cel_content_pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_page_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `nav_title` varchar(255) NOT NULL,
  `config` text NOT NULL,
  `sort` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_extensions`
--

CREATE TABLE IF NOT EXISTS `cel_extensions` (
  `extension_id` int(11) NOT NULL AUTO_INCREMENT,
  `extension_name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`extension_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_permissions`
--

CREATE TABLE IF NOT EXISTS `cel_permissions` (
  `permission_id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(255) NOT NULL,
  `method` varchar(255) NOT NULL,
  `param` varchar(255) NOT NULL,
  `title` varchar(128) NOT NULL,
  PRIMARY KEY (`permission_id`),
  KEY `controller` (`class`),
  KEY `param` (`param`),
  KEY `method` (`method`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;


INSERT INTO `cel_permissions` (`permission_id`, `class`, `method`, `param`, `title`) VALUES
(1, 'Admin_Controller_Photo', 'edit', 'own', 'Photo → edit → own photos'),
(2, 'Admin_Controller_Photo', 'add', '', 'Photo → add new'),
(3, 'Admin_Controller_Photo', 'delete', 'own', 'Photo → delete → own photos'),
(4, 'Admin_Controller_Photo', 'edit', 'other', 'Photo → edit → other''s photos'),
(5, 'Admin_Controller_User', 'edit', 'own', 'User → edit → own profile'),
(6, 'Admin_Controller_User', 'edit', 'other', 'User → edit → other''s profiles'),
(7, 'Admin_Controller_User', 'add', '', 'User → add new'),
(8, 'Admin_Controller_User', 'delete', 'own', 'User → delete → own profile'),
(9, 'Admin_Controller_User', 'delete', 'other', 'User → delete → other''s profiles'),
(10, 'Admin_Controller_User', 'permissions', 'own', 'User → set permissions → own'),
(11, 'Admin_Controller_User', 'permissions', 'other', 'User → set permissions → other users'),
(12, 'Admin_Controller_User', 'profile', 'own', 'User → view → own profile'),
(13, 'Admin_Controller_Comments', 'view', '', 'Comments → view'),
(14, 'Admin_Controller_Comments', 'edit', '', 'Comments → edit'),
(15, 'Admin_Controller_Photo', 'view', '', 'Photo → overview'),
(16, 'Admin_Controller_Comments', 'delete', '', 'Comments → delete'),
(17, 'Admin_Controller_User', 'view', '', 'User → view list'),
(18, 'Admin_Controller_Settings', 'edit', '', 'Settings → edit'),
(19, 'Admin_Controller_Settings', 'edit', 'system', 'Settings → edit → System'),
(20, 'Admin_Controller_Settings', 'edit', 'theme', 'Settings → edit → Theme'),
(21, 'Admin_Controller_Settings', 'edit', 'general', 'Settings → edit → General');
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_permissions_x_usergroups`
--

CREATE TABLE IF NOT EXISTS `cel_permissions_x_usergroups` (
  `usergroup_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_permissions_x_users`
--

CREATE TABLE IF NOT EXISTS `cel_permissions_x_users` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  UNIQUE KEY `user_id` (`user_id`,`permission_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_photo_archive_settings`
--

CREATE TABLE IF NOT EXISTS `cel_photo_archive_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `64` varchar(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`setting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_photo_categories`
--

CREATE TABLE IF NOT EXISTS `cel_photo_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` int(11) NOT NULL,
  `description` text NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_photo_comments`
--

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_photo_exif`
--

CREATE TABLE IF NOT EXISTS `cel_photo_exif` (
  `photo_id` int(11) NOT NULL,
  `exif_data` text NOT NULL,
  UNIQUE KEY `photo_id` (`photo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_photo_photos`
--

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
  PRIMARY KEY (`photo_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_photo_photos_x_category`
--

CREATE TABLE IF NOT EXISTS `cel_photo_photos_x_category` (
  `photo_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  KEY `photo_id` (`photo_id`,`category_id`),
  KEY `photo_id_2` (`photo_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_userdata`
--

CREATE TABLE IF NOT EXISTS `cel_userdata` (
  `user_id` int(11) NOT NULL,
  `firstname` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `bday` date NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_usergroups`
--

CREATE TABLE IF NOT EXISTS `cel_usergroups` (
  `usergroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_usergroup_id` int(11) NOT NULL,
  `owner_user_id` int(11) NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`usergroup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_userprops`
--

CREATE TABLE IF NOT EXISTS `cel_userprops` (
  `prop_id` int(11) NOT NULL AUTO_INCREMENT,
  `prop_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`prop_id`),
  KEY `prop_name` (`prop_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_userprops_x_users`
--

CREATE TABLE IF NOT EXISTS `cel_userprops_x_users` (
  `user_id` int(11) NOT NULL,
  `prop_id` int(11) NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `access_level` tinyint(4) NOT NULL,
  UNIQUE KEY `user_id_2` (`user_id`,`prop_id`),
  KEY `user_id` (`user_id`),
  KEY `prop_id` (`prop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_users`
--

CREATE TABLE IF NOT EXISTS `cel_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `loginhash` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `passconf` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cel_users_x_usergroups`
--

CREATE TABLE IF NOT EXISTS `cel_users_x_usergroups` (
  `rel_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `usergroup_id` int(11) NOT NULL,
  PRIMARY KEY (`rel_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `cel_userdata`
--
ALTER TABLE `cel_userdata`
  ADD CONSTRAINT `cel_userdata_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `cel_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
