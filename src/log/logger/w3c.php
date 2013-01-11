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
 * This class is designed to build log files based on the W3c specification
 * at: http://www.w3.org/TR/WD-logfile.html
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Log
 * @since       13.1
 */
class LoggerW3c extends LoggerFile
{
	/**
	 * @var    string  The format which each entry follows in the log file.  All fields must be
	 *                 named in all caps and be within curly brackets eg. {FOOBAR}.
	 * @since  13.1
	 */
	protected $format = '{DATE}	{TIME}	{PRIORITY}	{CLIENTIP}	{CATEGORY}	{MESSAGE}';

	/**
	 * Constructor.
	 *
	 * @param   array  &$options  Log object options.
	 *
	 * @since   13.1
	 */
	public function __construct(array &$options)
	{
		if (empty($options['text_file']))
		{
			$options['text_file'] = 'error.w3c.php';
		}

		parent::__construct($options);
	}
}
