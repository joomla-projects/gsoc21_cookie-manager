<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\View\Item;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Index view class for Finder.
 *
 * @since  5.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The indexed item
     *
     * @var  object
     *
     * @since  5.0.0
     */
    protected $item;

    /**
     * The associated terms
     *
     * @var  object[]
     *
     * @since  5.0.0
     */
    protected $terms;

    /**
     * The associated taxonomies
     *
     * @var  object[]
     *
     * @since  5.0.0
     */
    protected $taxonomies;

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function display($tpl = null)
    {
        $this->item       = $this->get('Item');
        $this->terms      = $this->get('Terms');
        $this->taxonomies = $this->get('Taxonomies');

        // Configure the toolbar.
        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Method to configure the toolbar for this view.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_FINDER_INDEX_TOOLBAR_TITLE'), 'search-plus finder');
        ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_finder&view=index');
    }
}
