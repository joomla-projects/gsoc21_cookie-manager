--
-- Table structure for table `#__cookiemanager_cookies`
--

CREATE TABLE IF NOT EXISTS `#__cookiemanager_cookies` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `cookie_name` varchar(255) NOT NULL,
  `cookie_desc` varchar(255) NOT NULL,
  `exp_period` varchar(20) NOT NULL,
  `exp_value` int NOT NULL DEFAULT 0,
  `catid` int NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 1,
  `ordering` int NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int unsigned NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int unsigned NOT NULL DEFAULT 0,
  KEY `idx_state` (`published`),
  KEY `idx_catid` (`catid`),
  KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`) VALUES
(0, 'com_cookiemanager', 'component', 'com_cookiemanager', '', 1, 1, 1, 0, 1, '', '{"policylink":"","position":"bottom"}', '');

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(0, 'plg_system_cookiemanager', 'plugin', 'cookiemanager', 'system', 0, 1, 1, 0, '', '', '', 0, '0000-00-00 00:00:00', 0, 0);
