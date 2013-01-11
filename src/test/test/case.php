<?php
/**
 * @package     Gris-Gris.Skeleton
 * @subpackage  Test
 *
 * @copyright   Copyright (C) 2013 Respective authors. All rights reserved.
 * @license     Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris\Test;

use PHPUnit_Framework_TestCase;

/**
 * Abstract test case class for unit testing.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Test
 * @since       13.1
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * Assigns mock callbacks to methods.
	 *
	 * @param   object  $mockObject  The mock object that the callbacks are being assigned to.
	 * @param   array   $array       An array of methods names to mock with callbacks.  This method assumes
	 *                               that the mock callback is named {mock}{method name}.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function assignMockCallbacks($mockObject, $array)
	{
		foreach ($array as $index => $method)
		{
			if (is_array($method))
			{
				$methodName = $index;
				$callback = $method;
			}
			else
			{
				$methodName = $method;
				$callback = array(get_called_class(), 'mock' . $method);
			}

			$mockObject->expects($this->any())
				->method($methodName)
				->will($this->returnCallback($callback));
		}
	}

	/**
	 * Assigns mock values to methods.
	 *
	 * @param   object  $mockObject  The mock object.
	 * @param   array   $array       An associative array of methods to mock with return
	 *                               values: string (method name) => mixed (return value)
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function assignMockReturns($mockObject, $array)
	{
		foreach ($array as $method => $return)
		{
			$mockObject->expects($this->any())
				->method($method)
				->will($this->returnValue($return));
		}
	}
}
