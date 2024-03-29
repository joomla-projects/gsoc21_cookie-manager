<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class to render a list view sidebar
 *
 * @since  3.0
 */
abstract class Sidebar
{
    /**
     * Menu entries
     *
     * @var    array
     * @since  3.0
     */
    protected static $entries = [];

    /**
     * Filters
     *
     * @var    array
     * @since  3.0
     */
    protected static $filters = [];

    /**
     * Value for the action attribute of the form.
     *
     * @var    string
     * @since  3.0
     */
    protected static $action = '';

    /**
     * Render the sidebar.
     *
     * @return  string  The necessary HTML to display the sidebar
     *
     * @since   3.0
     */
    public static function render()
    {
        // Collect display data
        $data                 = new \stdClass();
        $data->list           = static::getEntries();
        $data->filters        = static::getFilters();
        $data->action         = static::getAction();
        $data->displayMenu    = count($data->list);
        $data->displayFilters = count($data->filters);
        $data->hide           = Factory::getApplication()->getInput()->getBool('hidemainmenu');

        // Create a layout object and ask it to render the sidebar
        $layout      = new FileLayout('joomla.sidebars.submenu');

        return $layout->render($data);
    }

    /**
     * Method to add a menu item to submenu.
     *
     * @param   string  $name    Name of the menu item.
     * @param   string  $link    URL of the menu item.
     * @param   bool    $active  True if the item is active, false otherwise.
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function addEntry($name, $link = '', $active = false)
    {
        static::$entries[] = [$name, $link, $active];
    }

    /**
     * Returns an array of all submenu entries
     *
     * @return  array
     *
     * @since   3.0
     */
    public static function getEntries()
    {
        return static::$entries;
    }

    /**
     * Method to add a filter to the submenu
     *
     * @param   string  $label      Label for the menu item.
     * @param   string  $name       Name for the filter. Also used as id.
     * @param   string  $options    Options for the select field.
     * @param   bool    $noDefault  Don't show the label as the empty option
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function addFilter($label, $name, $options, $noDefault = false)
    {
        static::$filters[] = ['label' => $label, 'name' => $name, 'options' => $options, 'noDefault' => $noDefault];
    }

    /**
     * Returns an array of all filters
     *
     * @return  array
     *
     * @since   3.0
     */
    public static function getFilters()
    {
        return static::$filters;
    }

    /**
     * Set value for the action attribute of the filter form
     *
     * @param   string  $action  Value for the action attribute of the form
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function setAction($action)
    {
        static::$action = $action;
    }

    /**
     * Get value for the action attribute of the filter form
     *
     * @return  string
     *
     * @since   3.0
     */
    public static function getAction()
    {
        return static::$action;
    }
}
