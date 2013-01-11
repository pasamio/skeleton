<?php
/**
 * @package     Gris-Gris.Skeleton
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2013 Respective authors. All rights reserved.
 * @license     Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris\Service;

/**
 * Abstract JSON service class.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Service
 * @since       1.0
 */
abstract class Json extends Service
{
	/**
	 * Set a WebResponse object to the application with a JSON encoded body.  The second argument can be used to
	 * set the response status and code.  If none is set 200 OK will be used.
	 *
	 * @param   mixed   $body  The value to have JSON encoded to the body of the response object.
	 * @param   string  $type  The type of web response object to create.  Defaults to 200 OK.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setResponse($body, $type = 'Ok')
	{
		$response = $this->createResponse($type);

		$response->setContentType('application/json');
		$response->setBody(json_encode($body));

		$this->application->setResponse($response);
	}
}
