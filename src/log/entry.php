<?php
/**
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2013 Respective authors. All rights reserved.
 * @license     Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris\Log;

use DateTime;

/**
 * This class is designed to hold log entries for either writing to an engine, or for
 * supported engines, retrieving lists and building in memory (PHP based) search operations.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 * @since       13.1
 */
class Entry
{
	/**
	 * @var    string  Application responsible for log entry.
	 * @since  13.1
	 */
	public $category;

	/**
	 * @var    DateTime  The date the message was logged.
	 * @since  13.1
	 */
	public $date;

	/**
	 * @var    string  Message to be logged.
	 * @since  13.1
	 */
	public $message;

	/**
	 * @var    string  The priority of the message to be logged.
	 * @since  13.1
	 * @see    $priorities
	 */
	public $priority = Log::INFO;

	/**
	 * @var    array  List of available log priority levels [Based on the Syslog default levels].
	 * @since  13.1
	 */
	protected $priorities = array(
		Log::EMERGENCY,
		Log::ALERT,
		Log::CRITICAL,
		Log::ERROR,
		Log::WARNING,
		Log::NOTICE,
		Log::INFO,
		Log::DEBUG
	);

	/**
	 * Constructor
	 *
	 * @param   string  $message   The message to log.
	 * @param   string  $priority  Message priority based on {$this->priorities}.
	 * @param   string  $category  Type of entry
	 * @param   string  $date      Date of entry (defaults to now if not specified or blank)
	 *
	 * @since   13.1
	 */
	public function __construct($message, $priority = Log::INFO, $category = '', $date = null)
	{
		$this->message = (string) $message;

		// Sanitize the priority.
		if (!in_array($priority, $this->priorities, true))
		{
			$priority = Log::INFO;
		}
		$this->priority = $priority;

		// Sanitize category if it exists.
		if (!empty($category))
		{
			$this->category = (string) strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $category));
		}

		// Get the date as a DateTime object.
		$this->date = new DateTime($date ? $date : 'now');
	}
}
