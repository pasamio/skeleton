<?php
/**
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2013 Respective authors. All rights reserved.
 * @license     Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris\Log;

/**
 * This class is used to be the basis of logger classes to allow for defined functions
 * to exist regardless of the child class.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 * @since       13.1
 */
abstract class Logger
{
	/**
	 * @var    array  Options array for the Log instance.
	 * @since  13.1
	 */
	protected $options = array();

	/**
	 * @var    array  Translation array for LogEntry priorities to text strings.
	 * @since  13.1
	 */
	protected $priorities = array(
		Log::EMERGENCY => 'EMERGENCY',
		Log::ALERT => 'ALERT',
		Log::CRITICAL => 'CRITICAL',
		Log::ERROR => 'ERROR',
		Log::WARNING => 'WARNING',
		Log::NOTICE => 'NOTICE',
		Log::INFO => 'INFO',
		Log::DEBUG => 'DEBUG');

	/**
	 * Constructor.
	 *
	 * @param   array  &$options  Log object options.
	 *
	 * @since   13.1
	 */
	public function __construct(array &$options)
	{
		$this->options = & $options;
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   Entry  $entry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	abstract public function addEntry(Entry $entry);
}
