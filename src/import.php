<?php
/**
 *   ______             __                     ______             __
 *  /      \           |  \                   /      \           |  \
 * |  $$$$$$\  ______   \$$  _______         |  $$$$$$\  ______   \$$  _______
 * | $$ __\$$ /      \ |  \ /       \ ______ | $$ __\$$ /      \ |  \ /       \
 * | $$|    \|  $$$$$$\| $$|  $$$$$$$|      \| $$|    \|  $$$$$$\| $$|  $$$$$$$
 * | $$ \$$$$| $$   \$$| $$ \$$    \  \$$$$$$| $$ \$$$$| $$   \$$| $$ \$$    \
 * | $$__| $$| $$      | $$ _\$$$$$$\        | $$__| $$| $$      | $$ _\$$$$$$\
 *  \$$    $$| $$      | $$|       $$         \$$    $$| $$      | $$|       $$
 *   \$$$$$$  \$$       \$$ \$$$$$$$           \$$$$$$  \$$       \$$ \$$$$$$$
 *
 *   ______   __                  __             __
 *  /      \ |  \                |  \           |  \
 * |  $$$$$$\| $$   __   ______  | $$  ______  _| $$_     ______   _______
 * | $$___\$$| $$  /  \ /      \ | $$ /      \|   $$ \   /      \ |       \
 *  \$$    \ | $$_/  $$|  $$$$$$\| $$|  $$$$$$\\$$$$$$  |  $$$$$$\| $$$$$$$\
 *  _\$$$$$$\| $$   $$ | $$    $$| $$| $$    $$ | $$ __ | $$  | $$| $$  | $$
 * |  \__| $$| $$$$$$\ | $$$$$$$$| $$| $$$$$$$$ | $$|  \| $$__/ $$| $$  | $$
 *  \$$    $$| $$  \$$\ \$$     \| $$ \$$     \  \$$  $$ \$$    $$| $$  | $$
 *   \$$$$$$  \$$   \$$  \$$$$$$$ \$$  \$$$$$$$   \$$$$   \$$$$$$  \$$   \$$
 *
 * Bootstrap file for Gris-Gris Skeleton.  Including this file into your application will protect
 * you from evil and bring good luck.  It will also enable access to the skeleton libraries.
 *
 * @package    Gris-Gris.Skeleton
 *
 * @copyright  Copyright (C) 2013 Respective authors. All rights reserved.
 * @license    Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris;

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
	const LOWER_CASE = 1;
	const NATURAL_CASE = 2;
	const MIXED_CASE = 3;

	/**
	 * Container for already imported library paths.
	 *
	 * @var    array
	 * @since  13.1
	 */
	protected static $classes = array();

	/**
	 * Container for namespace => path map.
	 *
	 * @var    array
	 * @since  13.1
	*/
	protected static $namespaces = array();

	/**
	 * Method to discover classes of a given type in a given path.
	 *
	 * @param   string   $classPrefix  The class name prefix to use for discovery.
	 * @param   string   $parentPath   Full path to the parent folder for the classes to discover.
	 * @param   boolean  $force        True to overwrite the autoload path value for the class if it already exists.
	 * @param   boolean  $recurse      Recurse through all child directories as well as the parent path.
	 *
	 * @return  void
	 *
	 * @since   13.1
	*/
	public static function discover($classPrefix, $parentPath, $force = true, $recurse = false)
	{
		try
		{
			if ($recurse)
			{
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($parentPath),
					\RecursiveIteratorIterator::SELF_FIRST
				);
			}
			else
			{
				$iterator = new \DirectoryIterator($parentPath);
			}

			foreach ($iterator as $file)
			{
				$fileName = $file->getFilename();

				// Only load for php files.
				// Note: DirectoryIterator::getExtension only available PHP >= 5.3.6
				if ($file->isFile() && substr($fileName, strrpos($fileName, '.') + 1) == 'php')
				{
					// Get the class name and full path for each file.
					$class = strtolower($classPrefix . preg_replace('#\.php$#', '', $fileName));

					if (empty(self::$classes[$class]) || $force)
					{
						self::register($class, $file->getPath() . '/' . $fileName);
					}
				}
			}
		}
		catch (\UnexpectedValueException $e)
		{
			// Exception will be thrown if the path is not a directory. Ignore it.
		}
	}

	/**
	 * Method to get the list of registered classes and their respective file paths for the autoloader.
	 *
	 * @return  array  The array of class => path values for the autoloader.
	 *
	 * @since   13.1
	 */
	public static function getClassList()
	{
		return self::$classes;
	}

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
		$class = strtolower($class);

		if (class_exists($class, false))
		{
			return true;
		}

		if (isset(self::$classes[$class]))
		{
			include_once self::$classes[$class];

			return true;
		}

		return false;
	}

	/**
	 * Load a class based on namespace using the Lower Case strategy. This loader might be used when the namespace
	 * is lower case or camel case and the path lower case.
	 *
	 * @param   string  $class  The class (including namespace) to load.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   13.1
	 */
	public static function loadByNamespaceLowerCase($class)
	{
		if (class_exists($class, false))
		{
			return true;
		}

		// Get the root namespace name.
		$namespace = strstr($class, '\\', true);

		if (isset(self::$namespaces[$namespace]))
		{
			$class = str_replace($namespace, '', $class);

			// Create a lower case relative path.
			$relativePath = strtolower(str_replace('\\', '/', $class));

			// Iterate the registered root paths.
			foreach (self::$namespaces[$namespace] as $rootPath)
			{
				$path = $rootPath . '/' . $relativePath . '.php';

				if (file_exists($path))
				{
					return (bool) include_once $path;
				}
			}
		}

		return false;
	}

	/**
	 * Load a class based on namespace using the Natural Case strategy.  This loader might be used when the
	 * namespace case matches the path case.
	 *
	 * @param   string  $class  The class (including namespace) to load.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   13.1
	 */
	public static function loadByNamespaceNaturalCase($class)
	{
		if (class_exists($class, false))
		{
			return true;
		}

		// Get the root namespace name.
		$namespace = strstr($class, '\\', true);

		if (isset(self::$namespaces[$namespace]))
		{
			$class = str_replace($namespace, '', $class);

			// Create a relative path.
			$relativePath = str_replace('\\', '/', $class);

			// Iterate the registered root paths.
			foreach (self::$namespaces[$namespace] as $rootPath)
			{
				$path = $rootPath . '/' . $relativePath . '.php';

				if (file_exists($path))
				{
					return (bool) include_once $path;
				}
			}
		}

		return false;
	}

	/**
	 * Load a class based on namespace using the Mixed Case strategy.  This loader might be used when the
	 * namespace case matches the path case, or when the namespace is camel case and the path lower case.
	 *
	 * @param   string  $class  The class (including namespace) to load.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   13.1
	 */
	public static function loadByNamespaceMixedCase($class)
	{
		if (class_exists($class, false))
		{
			return true;
		}

		// Get the root namespace name.
		$namespace = strstr($class, '\\', true);

		if (isset(self::$namespaces[$namespace]))
		{
			$class = str_replace($namespace, '', $class);

			// Create a relative path.
			$relativePath = str_replace('\\', '/', $class);

			// Create a relative lower case path.
			$relativeLowPath = strtolower($relativePath);

			// Iterate the registered root paths.
			foreach (self::$namespaces[$namespace] as $rootPath)
			{
				// Create the full lower case path.
				$lowerPath = $rootPath . '/' . $relativeLowPath . '.php';

				if (file_exists($lowerPath))
				{
					return (bool) include_once $lowerPath;
				}

				// Create the full natural case path.
				$naturalPath = $rootPath . '/' . $relativePath . '.php';

				if (file_exists($naturalPath))
				{
					return (bool) include_once $naturalPath;
				}
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
			throw new \RuntimeException('Library path ' . $path . ' cannot be found.', 500);
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
	 * @param   integer  $caseStrategy      An option to define the class finding strategy for the namespace loader
	 *                                      depending on the namespace and class path case.
	 *                                      The possible values are :
	 *                                      Loader::LOWER_CASE : The namespace can be either lower case or camel case and the path lower case.
	 *                                      Loader::NATURAL_CASE : The namespace case matches the path case.
	 *                                      Loader::MIXED_CASE : It regroups option 1 and option 2.
	 * @param   boolean  $enableNamespaces  True to enable PHP namespace based class autoloading.
	 * @param   boolean  $enableClasses     True to enable class map based class loading.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public static function setup($caseStrategy = self::LOWER_CASE, $enableNamespaces = true, $enableClasses = true)
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
			switch ($caseStrategy)
			{
				case self::LOWER_CASE:
					spl_autoload_register(array(__CLASS__, 'loadByNamespaceLowerCase'));
					break;

				case self::NATURAL_CASE:
					spl_autoload_register(array(__CLASS__, 'loadByNamespaceNaturalCase'));
					break;

				case self::MIXED_CASE:
					spl_autoload_register(array(__CLASS__, 'loadByNamespaceMixedCase'));
					break;

				default:
					spl_autoload_register(array(__CLASS__, 'loadByNamespaceLowerCase'));
					break;
			}
		}
	}
}

// Here comes the hoodoo.
Loader::setup();
