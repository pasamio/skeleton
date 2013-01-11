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
use Grisgris\Database\Driver;

/**
 * This class is designed to output logs to a specific database table. Fields in this
 * table are based on the Syslog style of log output. This is designed to allow quick and
 * easy searching.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 * @since       13.1
 */
class LoggerDatabase extends Logger
{
	/**
	 * @var    Driver  The database driver object for the logger.
	 * @since  13.1
	 */
	protected $driver;

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

		if (!$this->options['driver'] instanceof Driver)
		{
			throw new InvalidArgumentException('No database driver specified for the logger.');
		}
		$this->driver =  $this->options['driver'];

		// The table name is independent of how we arrived at the connection object.
		$this->table = (empty($this->options['table'])) ? '#__log_entries' : $this->options['table'];
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
	public function addEntry(Entry $entry)
	{
		$entry->date = $entry->date->toSql(false, $this->dbo);
		$this->driver->insertObject($this->table, $entry);
	}
}
