<?php
/**
 * @package     Gris-Gris.Skeleton
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2013 Respective authors. All rights reserved.
 * @license     Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris\Service;

use InvalidArgumentException;
use Grisgris\Controller\Base;
use Grisgris\Application\WebResponse;

/**
 * Abstract service class.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Service
 * @since       1.0
 */
abstract class Service extends Base
{
	/**
	 * Set a WebResponse object to the application with a given body.  The second argument can be used to
	 * set the response status and code.  If none is set 200 OK will be used.
	 *
	 * @param   mixed   $body  The value to set to the body of the response object.
	 * @param   string  $type  The type of web response object to create.  Defaults to 200 OK.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setResponse($body, $type = 'Ok')
	{
		$response = $this->createResponse($type);
		$response->setBody($body);

		$this->application->setResponse($response);
	}

	/**
	 * Create a new WebResponse object by type.
	 *
	 * @param   string  $type  The type of web response object to create.
	 *
	 * @return  WebResponse
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException
	 */
	protected function createResponse($type)
	{
		$className = '\\Grisgris\\Application\\WebResponse' . ucfirst($type);
		if (!class_exists($className))
		{
			throw new InvalidArgumentException(sprintf('The response class for `%s` was not found.', $type));
		}
		$response = new $className($this->provider);

		return $response;
	}
}
