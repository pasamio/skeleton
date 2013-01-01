<?php
/**
 * Bootstrap file for Gris-Gris Skeleton in Phar phorm.
 *
 * @package    Gris-Gris.Skeleton
 *
 * @copyright  Copyright (C) 2013 Respective authors. All rights reserved.
 * @license    Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris;

\Phar::interceptFileFuncs();

// In the Pharchive using __DIR__ gives us unexpected and unwanted results... which reduces magic.
if (!defined('LIBRARY_PATH'))
{
	define('LIBRARY_PATH', 'phar://' . __FILE__);
}

require LIBRARY_PATH . '/import.php';

__HALT_COMPILER();?>