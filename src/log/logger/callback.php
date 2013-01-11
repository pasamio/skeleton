<?php
/**
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2013 Respective authors. All rights reserved.
 * @license     Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris\Log;

use InvalidArgumentException;

/**
 * This class allows logging to be handled by a callback function.
 * This allows unprecedented flexibility in the way logging can be handled.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 * @since       13.1
 */
class LoggerCallback extends Logger
{
	/**
	 * @var    callable  The function to call when an entry is added - should return True on success
	 * @since  13.1
	 */
	protected $callback;

	/**
	 * Constructor.
	 *
	 * @param   array  &$options  Log object options.
	 *
	 * @since   13.1
	 */
	public function __construct(array &$options)
	{
		parent::__construct($options);

		// Throw an exception if there is not a valid callback
		if (isset($this->options['callback']) && is_callable($this->options['callback']))
		{
			$this->callback = $this->options['callback'];
		}
		else
		{
			throw new InvalidArgumentException('LoggerCallback created without valid callback function.');
		}
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   Entry  $entry  The log entry object to add to the log.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   13.1
	 * @throws  LogException
	 */
	public function addEntry(Entry $entry)
	{
		call_user_func($this->callback, $entry);
	}
}
