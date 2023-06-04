<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.privacyconsent
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\PrivacyConsent;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Loads scripts and data for the consent banner
 *
 * @since  __DEPLOY_VERSION__
 */
trait ConsentBannerContentLoader
{
    /**
     * @var    \Joomla\Database\DatabaseDriver
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $db;

    /**
     * Not mandatory scripts loaded in the head of the document are marked and loaded after user gave consent only.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onBeforeCompileHead()
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa     = $this->getApplication()->getDocument()->getWebAssetManager();
        $assets = $wa->getAssets('script');

        if (count($assets) === 0) {
            return;
        }

        $this->markScripts($assets);

        // Finally load banner content with assets etc.
        $this->loadCoockieBannerContent();
    }

    /**
     * Collects data to build up and display the consent banner.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function loadCoockieBannerContent()
    {
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
        $document->addScriptOptions('plg_system_privacyconsent.categories', $event->getArgument('cookiecategories'));

        $event = new GenericEvent(
            'onLoadCookieScripts',
            [
                'context'       => $context,
                'cookiescripts' => [],
            ]
        );
        $this->getDispatcher()->dispatch('onLoadCookieScripts', $event);
        $document->addScriptOptions('plg_system_privacyconsent.scripts', $event->getArgument('cookiescripts'));

        $event = new GenericEvent(
            'onLoadCookies',
            [
                'context' => $context,
                'cookies' => [],
            ]
        );
        $this->getDispatcher()->dispatch('onLoadCookies', $event);
        $document->addScriptOptions('plg_system_privacyconsent.cookies', $event->getArgument('cookies'));

        $this->setConfig();
        $this->loadTexts();
        $this->loadAssets();
    }

    /**
     * Loads CSS and javascript files by the WebAssetManager
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function loadAssets()
    {
        $wa = $this->getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseScript(
            'plg_system_privacyconsent.script',
            'plg_system_privacyconsent/privacyconsent.min.js',
            ['dependencies' => ['cookieconsent']],
            ['defer'        => true],
        );
        $wa->registerAndUseStyle(
            'plg_system_privacyconsent.style',
            'plg_system_privacyconsent/privacyconsent.min.css',
            ['dependencies' => ['cookieconsent']],
            ['defer'        => true],
        );
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
        $this->getApplication()->getLanguage()->load('com_privacyconsent', JPATH_ADMINISTRATOR);

        Text::script('PLG_SYSTEM_PRIVACYCONSENT_BANNER_TITLE');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_BANNER_DESC');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_BANNER_BTN_ACCEPT_ALL');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_BANNER_BTN_ACCEPT_SELECTED');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_BANNER_BTN_REJECT_ALL');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_BANNER_BTN_SETTINGS');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_SETTINGS_TITLE');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_SETTINGS_BTN_SAVE');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_SETTINGS_BTN_ACCEPT_ALL');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_SETTINGS_BTN_REJECT_ALL');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_SETTINGS_BTN_CLOSE');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_TABLE_HEADERS_COL1');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_TABLE_HEADERS_COL2');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_TABLE_HEADERS_COL3');
        Text::script('PLG_SYSTEM_PRIVACYCONSENT_TABLE_HEADERS_COL4');
        Text::script('COM_PRIVACY_PREVIEW_BUTTON_TEXT');
        Text::script('COM_PRIVACY_VIEW_COOKIE_POLICY');
    }

    /**
     * Params are prepared for using them in the js cookie consent banner
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function setConfig()
    {
        $params = ComponentHelper::getParams('com_privacyconsent');

        $cookieManagerConfig = [
            'consent_modal' => [
                'position_x'            => $params->get('consent_modal_position_x', 'center'),
                'position_y'            => $params->get('consent_modal_position_y', 'bottom'),
                'transition'            => $params->get('consent_modal_transition', 'zoom'),
                'layout'                => $params->get('consent_modal_layout', 'box'),
                'swap_buttons'          => $params->get('consent_modal_swap_buttons', '0'),
                'primary_button_role'   => $params->get('consent_modal_primary_button_role', 'accept_all'),
                'secondary_button_role' => $params->get('consent_modal_secondary_button_role', 'accept_necessary'),
            ],
            'settings_modal' => [
                'position'   => $params->get('settings_modal_position', 'left'),
                'transition' => $params->get('settings_modal_transition', 'slide'),
                'layout'     => $params->get('settings_modal_layout', 'box'),
            ],
        ];
        $cookieManagerConfig['expiration']           = $params->get('consent_expiration', 30);
        $cookieManagerConfig['delay']                = $params->get('delay', 0);
        $cookieManagerConfig['force_consent']        = $params->get('force_consent', 0);
        $cookieManagerConfig['mode']                 = $params->get('mode', 'opt-in');
        $cookieManagerConfig['hide_from_bots']       = $params->get('hide_from_bots', 1);
        $cookieManagerConfig['remove_cookie_tables'] = $params->get('remove_cookie_tables', 0);
        $cookieManagerConfig['cookie_same_site']     = $params->get('cookie_same_site', 'Lax');

        $menuitem = $this->getApplication()->getMenu()->getItem($params->get('policylink', ''));
        if ($menuitem) {
            $cookieManagerConfig['policylink'] = HTMLHelper::_('link', Route::_($menuitem->link));
        }

        $this->getApplication()->getDocument()->addScriptOptions('plg_system_privacyconsent.config', $cookieManagerConfig);
    }

    /**
     * Additional scripts in the document header are marked with data-cookiecategory="<category>" and type="text/plain"
     * Necessary to block them first until user accepted their category usage.
     * Scripts from /media/system and /media/templates are marked with category "mandatory"
     *
     * @param WebAssetItem[]  $assets  array of web assets to mark
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function markScripts(array $assets)
    {
        $scripts = $this->getCookiemanagerScripts();

        foreach ($assets as $asset) {
            $uri           = $asset->getUri();
            $startOfName   = strrpos($uri, '/');
            $assetName     = $startOfName === false ? $uri : substr($uri, $startOfName + 1);
            $isMandatory   = str_contains($uri, '/media/system/') || str_contains($uri, '/media/templates/');
            $uncategorized = array_key_exists($assetName, $scripts) === false;
            $category      = $uncategorized ? 'unknown' : $scripts[$assetName];
            $type          = $asset->getAttribute('type');

            $asset->setAttribute('data-cookiecategory', $isMandatory ? 'mandatory' : $category);

            if (!$isMandatory) {
                if (!empty($type)) {
                    $asset->setAttribute('data-cookiemanager-old-type', $type);
                    if (!str_contains($type, 'json')) {
                        $asset->setAttribute('type', 'text/plain');
                    }
                }
            }
        }
    }

    /**
     * Loads all cookiemanager scripts with title and category alias
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
            ->from($db->quoteName('#__privacy_scripts', 's'))
            ->join(
                'LEFT',
                $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('s.catid')
            );
        return $db->setQuery($query)->loadAssocList('title', 'alias');
    }

    /**
     * Add cookies to list under their categories in the cookie consent banner
     *
     * @param   Event  $event  The event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onLoadCookies(Event $event)
    {
        $eventCookies = $event->getArgument('cookies', []);

        $db = $this->db;

        $query = $db->getQuery(true)
            ->select($db->quoteName(['c.id', 'c.alias', 'a.cookie_name', 'a.cookie_desc', 'a.exp_period', 'a.exp_value']))
            ->from($db->quoteName('#__categories', 'c'))
            ->join(
                'RIGHT',
                $db->quoteName('#__privacy_cookies', 'a') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid') . 'WHERE' . $db->quoteName('a.published') . ' =  1'
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
     * Add categories of cookies to deny or allow with the cookie consent banner
     *
     * @param   Event  $event  The event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onLoadCookieCategories(Event $event)
    {
        $eventCategories = $event->getArgument('cookiecategories', []);

        $db = $this->db;

        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title', 'alias', 'description', 'params']))
            ->from($db->quoteName('#__categories'))
            ->where(
                [
                    $db->quoteName('extension') . ' = ' . $db->quote('com_privacy'),
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
     * Loads information about the scripts the consent banner should display.
     *
     * @param   Event  $event  The event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onLoadCookieScripts(Event $event)
    {
        $eventScripts = $event->getArgument('cookiescripts', []);

        $db = $this->db;

        $query = $db->getQuery(true)
            ->select($db->quoteName(['s.type', 's.position', 's.code', 's.catid']))
            ->from($db->quoteName('#__privacy_scripts', 's'))
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
}
