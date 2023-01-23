<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

/**
 * System plugin to manage cookies.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemCookiemanager extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    \Joomla\Database\DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Add assets for the cookie banners.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{
		if (!$this->app->isClient('site'))
		{
			return;
		}

		$wa = $this->app->getDocument()->getWebAssetManager();
		$wa->registerAndUseScript(
				'plg_system_cookiemanager.script',
				'plg_system_cookiemanager/cookiemanager.min.js',
				['dependencies' => ['cookieconsent']],
			)
			->registerAndUseStyle(
				'plg_system_cookiemanager.style',
				'plg_system_cookiemanager/cookiemanager.min.css',
				['dependencies' => ['cookieconsent']],
			);

		$this->app->getLanguage()->load('com_cookiemanager', JPATH_ADMINISTRATOR);

		Text::script('PLG_SYSTEM_COOKIEMANAGER_BANNER_TITLE');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_BANNER_DESC');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_BANNER_BTN_ACCEPT_ALL');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_BANNER_BTN_REJECT_ALL');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_SETTINGS_TITLE');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_SETTINGS_BTN_SAVE');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_SETTINGS_BTN_ACCEPT_ALL');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_SETTINGS_BTN_REJECT_ALL');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_SETTINGS_BTN_CLOSE');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_TABLE_HEADERS_COL1');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_TABLE_HEADERS_COL2');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_TABLE_HEADERS_COL3');
		Text::script('PLG_SYSTEM_COOKIEMANAGER_TABLE_HEADERS_COL4');
		Text::script('COM_COOKIEMANAGER_PREVIEW_BUTTON_TEXT');
		Text::script('COM_COOKIEMANAGER_VIEW_COOKIE_POLICY');

		$params = ComponentHelper::getParams('com_cookiemanager');

		$cookieManagerConfig = [];
		$cookieManagerConfig['expiration'] = $params->get('consent_expiration', 30);
		$cookieManagerConfig['position'] = $params->get('modal_position', null);

		$menuitem = $this->app->getMenu()->getItem($params->get('policylink', ''));
		if ($menuitem) {
			$cookieManagerConfig['policylink'] = HTMLHelper::_('link', Route::_($menuitem->link));
		}

		$this->app->getDocument()->addScriptOptions('plg_system_cookiemanager.config', $cookieManagerConfig);

		$db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName(['c.id', 'c.alias', 'a.cookie_name', 'a.cookie_desc', 'a.exp_period', 'a.exp_value']))
			->from($db->quoteName('#__categories', 'c'))
			->join(
				'RIGHT',
				$db->quoteName('#__cookiemanager_cookies', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid') . 'WHERE' . $db->quoteName('a.published') . ' =  1'
			)
			->order($db->quoteName('lft'));

		$cookies = $db->setQuery($query)->loadObjectList();
		$this->app->getDocument()->addScriptOptions('plg_system_cookiemanager.cookies', $cookies);

		$query = $db->getQuery(true)
			->select($db->quoteName(['id', 'title', 'alias', 'description']))
			->from($db->quoteName('#__categories'))
			->where(
				[
					$db->quoteName('extension') . ' = ' . $db->quote('com_cookiemanager'),
					$db->quoteName('published') . ' =  1',
				]
			)
			->order($db->quoteName('lft'));

		$cookieCategories = $db->setQuery($query)->loadObjectList();
		$this->app->getDocument()->addScriptOptions('plg_system_cookiemanager.categories', $cookieCategories);

		$query = $db->getQuery(true)
			->select($db->quoteName(['a.type', 'a.position', 'a.code', 'a.catid']))
			->from($db->quoteName('#__cookiemanager_scripts', 'a'))
			->where($db->quoteName('a.published') . ' =  1')
			->join(
				'LEFT',
				$db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid')
			);

		$cookieScripts = $db->setQuery($query)->loadObjectList();
		$this->app->getDocument()->addScriptOptions('plg_system_cookiemanager.scripts', $cookieScripts);

		$cookieCodes = [];

		foreach ($cookieCategories as $category)
		{
			$cookie = $this->app->input->cookie->get('cookie_category_' . $category->alias);

			if (!isset($cookie) || $cookie === 'false')
			{
				$cookieCodes[$category->alias] = [];

				foreach ($cookieScripts as $script)
				{
					if ($category->id == $script->catid)
					{
						array_push($cookieCodes[$category->alias], $script);
					}
				}
			}
		}

		$this->app->getDocument()->addScriptOptions('plg_system_cookiemanager.codes', $cookieCodes);

		include PluginHelper::getLayoutPath('system', 'cookiemanager');
	}

	/**
	 * AJAX Handler
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAjaxCookiemanager()
	{
		$cookieConsentsData = $this->app->input->get('data', '', 'STRING');

		$cookieConsentsData = json_decode($cookieConsentsData);
		$ccuuid = bin2hex(random_bytes(32));
		$cookieConsentsData->ccuuid = $ccuuid;
		$cookieConsentsData->consent_date = Factory::getDate()->toSql();
		$cookieConsentsData->user_agent = $_SERVER['HTTP_USER_AGENT'];

		$this->db->insertObject('#__cookiemanager_consents', $cookieConsentsData);

		return $ccuuid;
	}
}
