<?php
/**
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2013 Respective authors. All rights reserved.
 * @license     Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris\Log;

use RuntimeException;

/**
 * Log Class
 *
 * This class hooks into the global log configuration settings to allow for user configured
 * logging events to be sent to where the user wishes them to be sent. On high load sites
 * Syslog is probably the best (pure PHP function), then the text file based loggers (CSV, W3c
 * or plain Formattedtext) and finally MySQL offers the most features (e.g. rapid searching)
 * but will incur a performance hit due to INSERT being issued.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 * @since       13.1
 */
class Log
{
	/**
	 * @var    integer  All log priorities.
	 * @since  13.1
	 */
	const ALL = 30719;

	/**
	 * @var    integer  The system is unusable.
	 * @since  13.1
	 */
	const EMERGENCY = 1;

	/**
	 * @var    integer  Action must be taken immediately.
	 * @since  13.1
	 */
	const ALERT = 2;

	/**
	 * @var    integer  Critical conditions.
	 * @since  13.1
	 */
	const CRITICAL = 4;

	/**
	 * @var    integer  Error conditions.
	 * @since  13.1
	 */
	const ERROR = 8;

	/**
	 * @var    integer  Warning conditions.
	 * @since  13.1
	 */
	const WARNING = 16;

	/**
	 * @var    integer  Normal, but significant condition.
	 * @since  13.1
	 */
	const NOTICE = 32;

	/**
	 * @var    integer  Informational message.
	 * @since  13.1
	 */
	const INFO = 64;

	/**
	 * @var    integer  Debugging message.
	 * @since  13.1
	 */
	const DEBUG = 128;

	/**
	 * @var    Log  The global Log instance.
	 * @since  13.1
	 */
	protected static $instance;

	/**
	 * @var    array  Container for Logger configurations.
	 * @since  13.1
	 */
	protected $configurations = array();

	/**
	 * @var    array  Container for Logger objects.
	 * @since  13.1
	 */
	protected $loggers = array();

	/**
	 * @var    array  Lookup array for loggers.
	 * @since  13.1
	 */
	protected $lookup = array();

	/**
	 * Constructor.
	 *
	 * @since   13.1
	 */
	protected function __construct()
	{
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   mixed    $entry     The Entry object to add to the log or the message for a new Entry object.
	 * @param   integer  $priority  Message priority.
	 * @param   string   $category  Type of entry
	 * @param   string   $date      Date of entry (defaults to now if not specified or blank)
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public static function add($entry, $priority = self::INFO, $category = '', $date = null)
	{
		// Automatically instantiate the singleton object if not already done.
		if (empty(self::$instance))
		{
			self::setInstance(new Log);
		}

		// If the entry object isn't a LogEntry object let's make one.
		if (!($entry instanceof Entry))
		{
			$entry = new Entry((string) $entry, $priority, $category, $date);
		}

		self::$instance->addLogEntry($entry);
	}

	/**
	 * Add a logger to the Log instance.  Loggers route log entries to the correct files/systems to be logged.
	 *
	 * @param   array    $options     The object configuration array.
	 * @param   integer  $priorities  Message priority
	 * @param   array    $categories  Types of entry
	 * @param   boolean  $exclude     If true, all categories will be logged except those in the $categories array
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public static function addLogger(array $options, $priorities = self::ALL, $categories = array(), $exclude = false)
	{
		// Automatically instantiate the singleton object if not already done.
		if (empty(self::$instance))
		{
			self::setInstance(new Log);
		}

		// The default logger is the formatted text log file.
		if (empty($options['logger']))
		{
			$options['logger'] = 'formattedtext';
		}
		$options['logger'] = strtolower($options['logger']);

		// Special case - if a Closure object is sent as the callback (in case of LogLoggerCallback)
		// Closure objects are not serializable so swap it out for a unique id first then back again later
		if (isset($options['callback']) && is_a($options['callback'], 'closure'))
		{
			$callback = $options['callback'];
			$options['callback'] = spl_object_hash($options['callback']);
		}

		// Generate a unique signature for the Log instance based on its options.
		$signature = md5(serialize($options));

		// Now that the options array has been serialized, swap the callback back in
		if (isset($callback))
		{
			$options['callback'] = $callback;
		}

		// Register the configuration if it doesn't exist.
		if (empty(self::$instance->configurations[$signature]))
		{
			self::$instance->configurations[$signature] = $options;
		}

		self::$instance->lookup[$signature] = (object) array(
			'priorities' => $priorities,
			'categories' => array_map('strtolower', (array) $categories),
			'exclude' => (bool) $exclude);
	}

	/**
	 * Returns a reference to the a Log object, only creating it if it doesn't already exist.
	 * Note: This is principally made available for testing and internal purposes.
	 *
	 * @param   Log  $instance  The logging object instance to be used by the static methods.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public static function setInstance($instance)
	{
		if (($instance instanceof Log) || $instance === null)
		{
			self::$instance = & $instance;
		}
	}

	/**
	 * Method to add an entry to the appropriate loggers.
	 *
	 * @param   Entry  $entry  The LogEntry object to send to the loggers.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @throws  RuntimeException
	 */
	protected function addLogEntry(Entry $entry)
	{
		// Find all the appropriate loggers based on priority and category for the entry.
		$loggers = $this->findLoggers($entry->priority, $entry->category);

		foreach ((array) $loggers as $signature)
		{
			// Attempt to instantiate the logger object if it doesn't already exist.
			if (empty($this->loggers[$signature]))
			{
				$class = __NAMESPACE__ . '\\Logger' . ucfirst($this->configurations[$signature]['logger']);
				if (class_exists($class))
				{
					$this->loggers[$signature] = new $class($this->configurations[$signature]);
				}
				else
				{
					throw new RuntimeException('Unable to create a LogLogger instance: ' . $class);
				}
			}

			// Add the entry to the logger.
			$this->loggers[$signature]->addEntry(clone($entry));
		}
	}

	/**
	 * Method to find the loggers to use based on priority and category values.
	 *
	 * @param   integer  $priority  Message priority.
	 * @param   string   $category  Type of entry
	 *
	 * @return  array  The array of loggers to use for the given priority and category values.
	 *
	 * @since   13.1
	 */
	protected function findLoggers($priority, $category)
	{
		$loggers = array();

		// Sanitize inputs.
		$priority = (int) $priority;
		$category = strtolower($category);

		// Let's go iterate over the loggers and get all the ones we need.
		foreach ((array) $this->lookup as $signature => $rules)
		{
			// Check to make sure the priority matches the logger.
			if ($priority & $rules->priorities)
			{
				if ($rules->exclude)
				{
					// If either there are no set categories or the category (including the empty case) is not in the list of excluded categories, add this logger.
					if (empty($rules->categories) || !in_array($category, $rules->categories))
					{
						$loggers[] = $signature;
					}
				}
				else
				{
					// If either there are no set categories (meaning all) or the specific category is set, add this logger.
					if (empty($category) || empty($rules->categories) || in_array($category, $rules->categories))
					{
						$loggers[] = $signature;
					}
				}
			}
		}

		return $loggers;
	}
}
