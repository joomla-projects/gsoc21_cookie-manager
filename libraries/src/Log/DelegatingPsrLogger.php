<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Delegating logger which delegates log messages received from the PSR-3 interface to the Joomla! Log object.
 *
 * @since  3.8.0
 * @internal
 */
final class DelegatingPsrLogger extends AbstractLogger
{
    /**
     * The Log instance to delegate messages to.
     *
     * @var    Log
     * @since  3.8.0
     */
    protected $logger;

    /**
     * Mapping array to map a PSR-3 level to a Joomla priority.
     *
     * @var    array
     * @since  3.8.0
     */
    protected $priorityMap = [
        LogLevel::EMERGENCY => Log::EMERGENCY,
        LogLevel::ALERT     => Log::ALERT,
        LogLevel::CRITICAL  => Log::CRITICAL,
        LogLevel::ERROR     => Log::ERROR,
        LogLevel::WARNING   => Log::WARNING,
        LogLevel::NOTICE    => Log::NOTICE,
        LogLevel::INFO      => Log::INFO,
        LogLevel::DEBUG     => Log::DEBUG,
    ];

    /**
     * Constructor.
     *
     * @param   Log  $logger  The Log instance to delegate messages to.
     *
     * @since   3.8.0
     */
    public function __construct(Log $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param   mixed   $level    The log level.
     * @param   string  $message  The log message.
     * @param   array   $context  Additional message context.
     *
     * @return  void
     *
     * @since   3.8.0
     * @throws  InvalidArgumentException
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        // Make sure the log level is valid
        if (!\array_key_exists($level, $this->priorityMap)) {
            throw new \InvalidArgumentException('An invalid log level has been given.');
        }

        // Map the level to Joomla's priority
        $priority = $this->priorityMap[$level];

        $category = null;
        $date     = null;

        // If a message category is given, map it
        if (!empty($context['category'])) {
            $category = $context['category'];
        }

        // If a message timestamp is given, map it
        if (!empty($context['date'])) {
            $date = $context['date'];
        }

        $this->logger->add((string) $message, $priority, $category, $date, $context);
    }
}
