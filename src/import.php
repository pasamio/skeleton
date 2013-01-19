<?php
/**
 * Bootstrap file for Gris-Gris Skeleton.  Including this file into your application will protect
 * you from evil and bring good luck.  It will also enable access to the skeleton libraries.
 *
 * @package    Gris-Gris.Skeleton
 *
 * @copyright  Copyright (C) 2013 Respective authors. All rights reserved.
 * @license    Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris;

use RuntimeException;

if (!defined('VERSION'))
{
	define('VERSION', 13.1);
}

if (!defined('LIBRARY_PATH'))
{
	define('LIBRARY_PATH', __DIR__);
}

// Detect the native operating system type.
if (!defined('IS_WIN'))
{
	define('IS_WIN', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? true : false);
}
if (!defined('IS_UNIX'))
{
	define('IS_UNIX', (IS_WIN === false) ? true : false);
}

/**
 * Static library loader class.
 *
 * @package  Gris-Gris.Skeleton
 * @since    13.1
 */
abstract class Loader
{
	/**
	 * @const  string  The namespace separator string.
	 * @since  13.1
	 */
	const NS_SEPARATOR = '\\';

	/**
	 * @var    array  Container for already registered library paths.
	 * @since  13.1
	 */
	protected static $classes = array();

	/**
	 * @var    array  Container for namespace => path map.
	 * @since  13.1
	*/
	protected static $namespaces = array();

	/**
	 * Method to get the list of registered namespaces.
	 *
	 * @return  array  The array of namespace => path values for the autoloader.
	 *
	 * @since   13.1
	 */
	public static function getNamespaces()
	{
		return self::$namespaces;
	}

	/**
	 * Load the file for a class.
	 *
	 * @param   string  $class  The class to be loaded.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   13.1
	 */
	public static function load($class)
	{
		if (class_exists($class, false))
		{
			return true;
		}

		$class = strtolower($class);
		if (isset(self::$classes[$class]))
		{
			return (bool) include_once self::$classes[$class];
		}

		return false;
	}

	/**
	 * Load a class based on namespace.
	 *
	 * @param   string  $class  The class (including namespace) to load.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   13.1
	 */
	public static function loadByNamespace($class)
	{
		if (class_exists($class, false))
		{
			return true;
		}

		// Sanitize and ensure we aren't dealing with stupid.
		$class = ltrim($class, self::NS_SEPARATOR);
		if (empty($class))
		{
			return false;
		}

		// Let's break down the namespace/classname separation.
		$lastNsPos = strripos($class, self::NS_SEPARATOR);
		$namespace = substr($class, 0, $lastNsPos);
		$className = substr($class, $lastNsPos + 1);

		// Find the registered namespace if it exists for the class.
		$registered = self::findRegisteredNamespace($namespace);
		$namespace = trim(str_replace($registered, '', $namespace), self::NS_SEPARATOR);
		if (!$registered)
		{
			return false;
		}

		$classPath[] = DIRECTORY_SEPARATOR . strtolower(trim(str_replace(self::NS_SEPARATOR, DIRECTORY_SEPARATOR, $namespace), DIRECTORY_SEPARATOR));
		$classPath[] = DIRECTORY_SEPARATOR . strtolower(trim(implode(DIRECTORY_SEPARATOR, preg_split('/(?<=[a-z0-9])(?=[A-Z])/x', $className)), DIRECTORY_SEPARATOR));
		$classPath = implode($classPath);

		// Iterate the paths.
		foreach (self::$namespaces[$registered] as $rootPath)
		{
			$path = $rootPath . $classPath . '.php';
			if (file_exists($path))
			{
				return (bool) include_once $path;
			}
		}

		return false;
	}

	/**
	 * Directly register a class to the autoload list.
	 *
	 * @param   string   $class  The class name to register.
	 * @param   string   $path   Full path to the file that holds the class to register.
	 * @param   boolean  $force  True to overwrite the autoload path value for the class if it already exists.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public static function register($class, $path, $force = true)
	{
		$class = strtolower($class);

		// Only attempt to register the class if the name and file exist.
		if (!empty($class) && is_file($path))
		{
			if (empty(self::$classes[$class]) || $force)
			{
				self::$classes[$class] = $path;
			}
		}
	}

	/**
	 * Register a namespace to the autoloader. When loaded, namespace paths are searched in a "last in, first out" order.
	 *
	 * @param   string   $namespace  A case sensitive Namespace to register.
	 * @param   string   $path       A case sensitive absolute file path to the library root where classes of the given namespace can be found.
	 * @param   boolean  $reset      True to reset the namespace with only the given lookup path.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @since   13.1
	 */
	public static function registerNamespace($namespace, $path, $reset = false)
	{
		if (!file_exists($path))
		{
			throw new RuntimeException('Library path ' . $path . ' cannot be found.', 500);
		}

		// If the namespace is not yet registered or we have an explicit reset flag then set the path.
		if (!isset(self::$namespaces[$namespace]) || $reset)
		{
			self::$namespaces[$namespace] = array($path);
		}
		else
		{
			array_unshift(self::$namespaces[$namespace], $path);
		}
	}

	/**
	 * Method to setup the autoloaders.  Since the SPL autoloaders are called in a queue we will add our explicit
	 * class-registration based loader first, then fall back on the autoloader based on conventions.  This will allow
	 * people to register a class in a specific location and override libraries as was previously possible.
	 *
	 * @param   boolean  $enableNamespaces  True to enable PHP namespace based class autoloading.
	 * @param   boolean  $enableClasses     True to enable class map based class loading.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public static function setup($enableNamespaces = true, $enableClasses = true)
	{
		// Register the Gris-Gris Skeleton libraries.
		self::registerNamespace('Grisgris', LIBRARY_PATH);

		// Register the class map based autoloader if required.
		if ($enableClasses)
		{
			spl_autoload_register(array(__CLASS__, 'load'));
		}

		if ($enableNamespaces)
		{
			spl_autoload_register(array(__CLASS__, 'loadByNamespace'));
		}
	}

	/**
	 * Find a registered namespace for a given namespace.
	 *
	 * @param   string  $namespace  The namespace to look up.
	 *
	 * @return  mixed  Registered namespace string if found, boolean false if not.array
	 *
	 * @since   13.1
	 */
	protected static function findRegisteredNamespace($namespace)
	{
		$parts = explode(self::NS_SEPARATOR, $namespace);

		for ($i = count($parts); $i > 0; $i--)
		{
			$lookup = implode(self::NS_SEPARATOR, array_slice($parts, 0, $i));
			foreach (array_keys(self::$namespaces) as $ns)
			{
				if ($lookup === $ns)
				{
					return $ns;
				}
			}
		}

		return false;
	}
}

// Here comes the hoodoo.
Loader::setup();

// Register classes for compatability with PHP 5.3
if (version_compare(PHP_VERSION, '5.4.0', '<'))
{
	Loader::register('JsonSerializable', LIBRARY_PATH . '/compat/jsonserializable.php');
}
