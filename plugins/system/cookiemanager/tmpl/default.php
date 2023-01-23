<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$consentBannerBody = '<p>' . Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_DESCRIPTION') . '</p>';
$consentBannerBody .= '<h5>' . Text::_('COM_COOKIEMANAGER_MANAGE_CONSENT_PREFERENCES') . '</h5><ul>';
$consentBannerBody .= '</ul>';

echo HTMLHelper::_(
	'bootstrap.renderModal',
	'consentBanner',
	[
		'title' => Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_TITLE'),
		'footer' => '<button type="button" id="confirmChoice" data-bs-dismiss="modal" class="btn btn-info">'
			. Text::_('COM_COOKIEMANAGER_CONFIRM_MY_CHOICES_BUTTON_TEXT') . '</button>'
			. '<button type="button" data-bs-toggle="modal" data-bs-dismiss="modal" data-bs-target="#settingsBanner" class="btn btn-info" >'
			. Text::_('COM_COOKIEMANAGER_MORE_DETAILS') . '</button>'
			. '<button type="button" data-button="acceptAllCookies" data-bs-dismiss="modal" class="btn btn-info">'
			. Text::_('COM_COOKIEMANAGER_ACCEPT_ALL_COOKIES_BUTTON_TEXT') . '</button>',

	],
	$consentBannerBody
);

$settingsBannerBody = '<p>' . Text::_('COM_COOKIEMANAGER_PREFERENCES_DESCRIPTION') . '</p>';

$settingsBannerBody .= '<p>' . Text::_('COM_COOKIEMANAGER_FIELD_CONSENT_OPT_IN_LABEL') . ': <span id="consent-opt-in"></span></p>'
	. '<p> ' . Text::_('COM_COOKIEMANAGER_CONSENT_ID') . ': <span id="ccuuid"></span></p>'
	. '<p>' . Text::_('COM_COOKIEMANAGER_FIELD_CONSENT_DATE_LABEL') . ': <span id="consent-date"></span></p>';

echo HTMLHelper::_(
	'bootstrap.renderModal',
	'settingsBanner',
	[
		'title' => Text::_('COM_COOKIEMANAGER_PREFERENCES_TITLE'),
		'footer' => '<button type="button" id="confirmSettingsChoice" class="btn btn-info" data-bs-dismiss="modal">'
			. Text::_('COM_COOKIEMANAGER_CONFIRM_MY_CHOICES_BUTTON_TEXT') . '</button>'
			. '<button type="button" data-button="acceptAllCookies" class="btn btn-info" data-bs-dismiss="modal">'
			. Text::_('COM_COOKIEMANAGER_ACCEPT_ALL_COOKIES_BUTTON_TEXT') . '</button>'
	],
	$settingsBannerBody
);
