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
 * Joomla Echo logger class.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 * @since       13.1
 */
class LoggerOut extends Logger
{
	/**
	 * @var    string  Value to use at the end of an echoed log entry to separate lines.
	 * @since  13.1
	 */
	protected $lineSeparator = "\n";

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

		if (!empty($this->options['line_separator']))
		{
			$this->lineSeparator = $this->options['line_separator'];
		}
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   LogEntry  $entry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function addEntry(Entry $entry)
	{
		echo $this->priorities[$entry->priority] . ': '
			. $entry->message . (empty($entry->category) ? '' : ' [' . $entry->category . ']')
			. $this->lineSeparator;
	}
}
