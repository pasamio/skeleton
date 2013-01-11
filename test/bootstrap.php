<?php
/**
 * PHPUnit bootstrap file for Gris-Gris Skeleton.
 *
 * @package    Gris-Gris.Skeleton
 *
 * @copyright  Copyright (C) 2013 Respective authors. All rights reserved.
 * @license    Licensed under the MIT License; see LICENSE.md
 * @link       http://www.phpunit.de/manual/current/en/installation.html
 */

namespace Grisgris;

// Fix up the environment just to make sure we are all good.
@ini_set('magic_quotes_runtime', 0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * The PHP garbage collector can be too aggressive in closing circular references before they are no longer needed.  This can cause
 * segfaults during long, memory-intensive processes such as testing large test suites and collecting coverage data.  We explicitly
 * disable garbage collection during the execution of PHPUnit processes so that we (hopefully) don't run into these issues going
 * forwards.  This is only a problem PHP 5.3+.
 */
gc_disable();

// Conjure the Gris-Gris Skeleton.
require_once dirname(realpath(__DIR__)) . '/src/import.php';

