<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Cookiemanager\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * System plugin to manage cookies.
 *
 * @since  __DEPLOY_VERSION__
 */
class Cookiemanager extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Database object.
     *
     * @var    DatabaseInterface
     * @since  __DEPLOY_VERSION__
     */
    protected $db;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  - The method name to call (priority defaults to 0)
     *  - An array composed of the method name to call and the priority
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        if (Factory::getApplication()->isClient('site')) {
            return [
                'onBeforeCompileHead'    => ['initialize', Priority::LOW],
                'onBeforeRender'         => ['markScripts', Priority::LOW],
                'onLoadCookies'          => 'addCookies',
                'onLoadCookieCategories' => 'addCategories',
                'onLoadCookieScripts'    => 'addScripts',
            ];
        }
        return [];
    }

    /**
     * Add assets for the cookie banners.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function initialize()
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        $document = $this->getApplication()->getDocument();
        $context  = $this->getApplication()->getInput()->get('option') . '.' . $this->getApplication()->getInput()->get('view');

        $event = new GenericEvent(
            'onLoadCookieCategories',
            [
                'context'          => $context,
                'cookiecategories' => [],
            ]
        );
        $this->getDispatcher()->dispatch('onLoadCookieCategories', $event);
        $document->addScriptOptions('plg_system_cookiemanager.categories', $event->getArgument('cookiecategories'));

        $event = new GenericEvent(
            'onLoadCookieScripts',
            [
                'context'       => $context,
                'cookiescripts' => [],
            ]
        );
        $this->getDispatcher()->dispatch('onLoadCookieScripts', $event);
        $document->addScriptOptions('plg_system_cookiemanager.scripts', $event->getArgument('cookiescripts'));

        $event = new GenericEvent(
            'onLoadCookies',
            [
                'context' => $context,
                'cookies' => [],
            ]
        );
        $this->getDispatcher()->dispatch('onLoadCookies', $event);
        $document->addScriptOptions('plg_system_cookiemanager.cookies', $event->getArgument('cookies'));

        $this->setConfig();
        $this->loadTexts();
        $this->loadAssets();
    }

    /**
     * Additional scripts in the document header are marked with data-cookiecategory="their category" and type="text/plain"
     * Necessary to block them first until user accepted their category usage.
     * Scripts from /media/system and /media/templates are marked with category "mandatory"
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function markScripts()
    {
        $wa     = $this->getApplication()->getDocument()->getWebAssetManager();
        $assets = $wa->getAssets('script');

        if (count($assets) === 0) {
            return;
        }

        $scripts = $this->getCookiemanagerScripts();

        foreach ($assets as $asset) {
            $uri           = $asset->getUri();
            $startOfName   = strrpos($uri, '/');
            $assetName     = $startOfName === false ? $uri : substr($uri, $startOfName + 1);
            $isMandatory   = str_contains($uri, '/media/system/') || str_contains($uri, '/media/templates/');
            $uncategorized = array_key_exists($assetName, $scripts) === false;
            $category      = $uncategorized ? 'unknown' : $scripts[$assetName];

            $asset->setAttribute('data-cookiecategory', $isMandatory ? 'mandatory' : $category);

            if (!$isMandatory) {
                $asset->setOption('type', 'text/plain');
            }
        }
    }

    /**
     * Loads all cookiemanager scripts with title and catid
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getCookiemanagerScripts()
    {
        $db = $this->db;

        $query = $db->getQuery(true)
            ->select($db->quoteName(['s.title', 's.catid', 'c.alias']))
            ->from($db->quoteName('#__cookiemanager_scripts', 's'))
            ->join(
                'LEFT',
                $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('s.catid')
            );
        return $db->setQuery($query)->loadAssocList('title', 'alias');
    }

    /**
     * Set texts in the current language for javascript
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function loadTexts()
    {
        $this->getApplication()->getLanguage()->load('com_cookiemanager', JPATH_ADMINISTRATOR);

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
    }

    /**
     * Loads CSS and javascript files through the WebAssetManager
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function loadAssets()
    {
        $wa = $this->getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseScript(
            'plg_system_cookiemanager.script',
            'plg_system_cookiemanager/cookiemanager.min.js',
            ['dependencies' => ['cookieconsent']],
            ['defer' => true],
        );
        $wa->registerAndUseStyle(
            'plg_system_cookiemanager.style',
            'plg_system_cookiemanager/cookiemanager.min.css',
            ['dependencies' => ['cookieconsent']],
            ['defer' => true],
        );
    }

    /**
     * Params are prepared for using them in the cookie manager javascript
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setConfig()
    {
        $params = ComponentHelper::getParams('com_cookiemanager');

        $cookieManagerConfig = [];
        $cookieManagerConfig['expiration'] = $params->get('consent_expiration', 30);
        $cookieManagerConfig['position'] = $params->get('modal_position', null);

        $menuitem = $this->getApplication()->getMenu()->getItem($params->get('policylink', ''));
        if ($menuitem) {
            $cookieManagerConfig['policylink'] = HTMLHelper::_('link', Route::_($menuitem->link));
        }

        $this->getApplication()->getDocument()->addScriptOptions('plg_system_cookiemanager.config', $cookieManagerConfig);
    }

    /**
     * Add cookies to list under their categories in the cookie manager
     *
     * @param   Event  $event  The event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function addCookies(Event $event)
    {
        $eventCookies = $event->getArgument('cookies', []);

        $db = $this->db;

        $query = $db->getQuery(true)
            ->select($db->quoteName(['c.id', 'c.alias', 'a.cookie_name', 'a.cookie_desc', 'a.exp_period', 'a.exp_value']))
            ->from($db->quoteName('#__categories', 'c'))
            ->join(
                'RIGHT',
                $db->quoteName('#__cookiemanager_cookies', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid') . 'WHERE' . $db->quoteName('a.published') . ' =  1'
            )
            ->order($db->quoteName('lft'));
        $cookies = $db->setQuery($query)->loadObjectList();

        $eventCookies = array_merge(
            $eventCookies,
            $cookies,
        );

        $event->setArgument('cookies', $eventCookies);
    }

    /**
     * Add categories of cookies to deny or allow with the cookie manager
     *
     * @param   Event  $event  The event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function addCategories(Event $event)
    {
        $eventCategories = $event->getArgument('cookiecategories', []);

        $db = $this->db;

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
        $categories = $db->setQuery($query)->loadObjectList();

        $eventCategories = array_merge(
            $eventCategories,
            $categories,
        );

        $event->setArgument('cookiecategories', $eventCategories);
    }

    /**
     * Add scripts to deny or allow with the cookie manager
     *
     * @param   Event  $event  The event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function addScripts(Event $event)
    {
        $eventScripts = $event->getArgument('cookiescripts', []);

        $db = $this->db;

        $query = $db->getQuery(true)
            ->select($db->quoteName(['s.type', 's.position', 's.code', 's.catid']))
            ->from($db->quoteName('#__cookiemanager_scripts', 's'))
            ->where($db->quoteName('s.published') . ' =  1')
            ->join(
                'LEFT',
                $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('s.catid')
            );
        $scripts = $db->setQuery($query)->loadObjectList();

        $eventScripts = array_merge(
            $eventScripts,
            $scripts,
        );

        $event->setArgument('cookiescripts', $eventScripts);
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
        $cookieConsentsData = $this->getApplication()->input->get('data', '', 'STRING');

        $cookieConsentsData = json_decode($cookieConsentsData);
        $ccuuid = bin2hex(random_bytes(32));
        $cookieConsentsData->ccuuid = $ccuuid;
        $cookieConsentsData->consent_date = Factory::getDate()->toSql();
        $cookieConsentsData->user_agent = $_SERVER['HTTP_USER_AGENT'];

        $this->getDatabase()->insertObject('#__cookiemanager_consents', $cookieConsentsData);

        return $ccuuid;
    }
}
