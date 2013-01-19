<?php
/**
 * @package     Gris-Gris.Skeleton
 * @subpackage  Compat
 *
 * @copyright   Copyright (C) 2013 Respective authors. All rights reserved.
 * @license     Licensed under the MIT License; see LICENSE.md
 */

/**
 * JsonSerializable interface. This file should only be loaded on PHP < 5.4.  It allows us to implement
 * it in classes without requiring PHP 5.4.
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Compat
 * @link        http://www.php.net/manual/en/jsonserializable.jsonserialize.php
 * @since       13.1
 */
interface JsonSerializable
{
	/**
	 * Return data which should be serialized by json_encode().
	 *
	 * @return  mixed
	 *
	 * @since   13.1
	 */
	public function jsonSerialize();
}
