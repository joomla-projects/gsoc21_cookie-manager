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

$sitemenu = $this->app->getMenu();
$menuitem = $sitemenu->getItem($params->get('policylink', ''));

$consentBannerBody = '<p>' . Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_DESCRIPTION') . '</p>';

if ($menuitem)
{
	$consentBannerBody .= '<p>' . HTMLHelper::_('link', Route::_($menuitem->link), Text::_('COM_COOKIEMANAGER_VIEW_COOKIE_POLICY')) . '</p>';
}

$consentBannerBody .= '<h5>' . Text::_('COM_COOKIEMANAGER_MANAGE_CONSENT_PREFERENCES') . '</h5><ul>';

foreach ($this->cookieCategories as $category)
{
	foreach ($this->cookies as $cookieValue)
	{
		if (!empty($category))
		{
			if ($category->id == $cookieValue->id)
			{
				$consentBannerBody .= '<li class="cookie-cat form-check form-check-inline">'
					. '<label>' . $category->title . '<span class="ms-4 form-check-inline form-switch">'
					. '<input class="form-check-input" data-cookiecategory="' . $category->alias . '" type="checkbox">'
					. '</span></label></li>';
				break;
			}
		}
	}
}

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

if ($menuitem)
{
	$settingsBannerBody .= '<p>' . HTMLHelper::_('link', Route::_($menuitem->link), Text::_('COM_COOKIEMANAGER_VIEW_COOKIE_POLICY')) . '</p>';
}

$settingsBannerBody .= '<p>' . Text::_('COM_COOKIEMANAGER_FIELD_CONSENT_OPT_IN_LABEL') . ': <span id="consent-opt-in"></span></p>'
	. '<p> ' . Text::_('COM_COOKIEMANAGER_CONSENT_ID') . ': <span id="ccuuid"></span></p>'
	. '<p>' . Text::_('COM_COOKIEMANAGER_FIELD_CONSENT_DATE_LABEL') . ': <span id="consent-date"></span></p>';

foreach ($this->cookieCategories as $catKey => $catValue)
{
	$hasCookies = true;
	foreach ($this->cookies as $cookie)
	{
		if (!empty($cookie))
		{
			if ($catValue->id == $cookie->id)
			{
				if ($hasCookies)
				{
					$settingsBannerBody .= '<h4>' . $catValue->title . '<span class="form-check-inline form-switch float-end">' .
						'<input class="form-check-input" type="checkbox" data-cookie-category="' . $catValue->alias . '"></span></h4>' . $catValue->description;
					$settingsBannerBody .= '<a class="text-decoration-none" data-bs-toggle="collapse" href="#' . $catValue->alias . '" role="button" aria-expanded="false" '
						. 'aria-controls="' . $catValue->alias . '">' . Text::_('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT') . '</a>'
						. '<div class="collapse" id="' . $catValue->alias . '">';
					$table = '<table class="table"><thead><tr>'
						. '<th scope="col">' . Text::_('COM_COOKIEMANAGER_TABLE_HEAD_COOKIENAME') . '</th>'
						. '<th scope="col">' . Text::_('COM_COOKIEMANAGER_TABLE_HEAD_DESCRIPTION') . '</th>'
						. '<th scope="col">' . Text::_('COM_COOKIEMANAGER_TABLE_HEAD_EXPIRATION') . '</th>'
						. '</tr></thead><tbody>';
					$hasCookies = false;
				}

				if ($cookie->exp_period == -1)
				{
					$cookie->exp_period = "Forever";
					$cookie->exp_value = "";
				}
				elseif ($cookie->exp_period == 0)
				{
					$cookie->exp_period = "Session";
					$cookie->exp_value = "";
				}

				$table .= '<tr>'
					. '<td>' . $cookie->cookie_name . '</td>'
					. '<td>' . $cookie->cookie_desc . '</td>'
					. '<td>' . $cookie->exp_value . ' ' . $cookie->exp_period . '</td>'
					. '</tr>';
			}
		}
	}

	if (!$hasCookies)
	{
		$table .= '</tbody></table>';
		$settingsBannerBody .= $table . '</div>';
	}
}

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
