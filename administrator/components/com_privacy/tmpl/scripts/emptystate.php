<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_PRIVACY_SCRIPTS',
    'formURL'    => 'index.php?option=com_privacy&view=scripts',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage',
    'icon'       => 'icon-code',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_privacy') || count($user->getAuthorisedCategories('com_privacy', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_privacy&task=script.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
