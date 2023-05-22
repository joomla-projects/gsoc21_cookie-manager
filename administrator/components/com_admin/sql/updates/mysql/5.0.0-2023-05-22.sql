--
-- Table structure for table `#__privacy_cookies`
--

CREATE TABLE IF NOT EXISTS `#__privacy_cookies` (
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

-- --------------------------------------------------------

--
-- Table structure for table `#__privacy_scripts`
--

CREATE TABLE IF NOT EXISTS `#__privacy_scripts` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `alias` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `position` int NOT NULL DEFAULT 4,
  `type` int NOT NULL DEFAULT 1,
  `code` text NOT NULL,
  `catid` int NOT NULL DEFAULT 0,
  `published` tinyint NOT NULL DEFAULT 1,
  `ordering` int NOT NULL DEFAULT 0,
  KEY `idx_state` (`published`),
  KEY `idx_catid` (`catid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `#__privacy_cookie_consents`
--

CREATE TABLE IF NOT EXISTS `#__privacy_cookie_consents` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `uuid` varchar(32) NOT NULL,
  `ccuuid` varchar(64) NOT NULL,
  `consent_opt_in` varchar(255) NOT NULL,
  `consent_opt_out` varchar(255) NOT NULL,
  `consent_date` datetime NOT NULL,
  `user_agent` varchar(150) NOT NULL,
  `url` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci;

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`) VALUES
(0, 'plg_system_cookiemanager', 'plugin', 'cookiemanager', 'system', 0, 1, 1, 0, 1, '', '', '');
