<?php

/**
 * @package    Joomla.API
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Global definitions
$parts = explode(DIRECTORY_SEPARATOR, JPATH_BASE);
array_pop($parts);

// Defines.
define('JPATH_ROOT', implode(DIRECTORY_SEPARATOR, $parts));
define('JPATH_SITE', JPATH_ROOT);
define('JPATH_PUBLIC', JPATH_ROOT);
define('JPATH_CONFIGURATION', JPATH_ROOT);
define('JPATH_ADMINISTRATOR', JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator');
define('JPATH_LIBRARIES', JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries');
define('JPATH_PLUGINS', JPATH_ROOT . DIRECTORY_SEPARATOR . 'plugins');
define('JPATH_INSTALLATION', JPATH_ROOT . DIRECTORY_SEPARATOR . 'installation');
define('JPATH_THEMES', JPATH_BASE . DIRECTORY_SEPARATOR . 'templates');
define('JPATH_CACHE', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'cache');
define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'manifests');
define('JPATH_API', JPATH_ROOT . DIRECTORY_SEPARATOR . 'api');
define('JPATH_CLI', JPATH_ROOT . DIRECTORY_SEPARATOR . 'cli');
