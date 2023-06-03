<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.privacyconsent
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\PrivacyConsent;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Offers ajax function to store selected user consent
 *
 * @since  __DEPLOY_VERSION__
 */
trait AjaxHandlerConsent
{
    /**
     * AJAX Handler
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onAjaxPrivacyconsent()
    {
        $cookieConsentsData = $this->getApplication()->input->get('data', '', 'STRING');

        $cookieConsentsData = json_decode($cookieConsentsData);
        $ccuuid = bin2hex(random_bytes(32));
        $cookieConsentsData->ccuuid = $ccuuid;
        $cookieConsentsData->consent_date = Factory::getDate()->toSql();
        $cookieConsentsData->user_agent = $_SERVER['HTTP_USER_AGENT'];

        $this->getDatabase()->insertObject('#__privacy_consents', $cookieConsentsData);

        return $ccuuid;
    }
}
