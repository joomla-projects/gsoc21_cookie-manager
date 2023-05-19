<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Consent Table class.
 *
 * @since   __DEPLOY_VERSION__
 */
class ConsentTable extends Table
{
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $_supportNullValue = true;

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__privacy_cookie_consents', 'id', $db);
    }

    /**
     * Stores consents.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }

    /**
     * Overloaded check function
     *
     * @return  boolean  True on success, false on failure
     *
     * @see     Table::check
     * @since   __DEPLOY_VERSION__
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return true;
    }
}
