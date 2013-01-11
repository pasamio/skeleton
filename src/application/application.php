<?php
/**
 * @package     Gris-Gris.Skeleton
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2013 Respective authors. All rights reserved.
 * @license     Licensed under the MIT License; see LICENSE.md
 */

namespace Grisgris\Application;

use Grisgris\Provider\Provider;
use Grisgris\Event\Dispatcher;
use Grisgris\Event\Event;
use Grisgris\Identity\Identity;
use Grisgris\Input\Input;
use Grisgris\Language\Language;
use Grisgris\Registry\Registry;

/**
 * Base Application Class
 *
 * @package     Gris-Gris.Skeleton
 * @subpackage  Application
 * @since       13.1
 */
abstract class Application
{
	/**
	 * @var    Input  The application input object.
	 * @since  13.1
	 */
	public $input;

	/**
	 * @var    Provider  A provider for dependencies.
	 * @since  13.1
	 */
	public $provider;

	/**
	 * @var    Registry  The application configuration object.
	 * @since  13.1
	 */
	protected $config;

	/**
	 * @var    Dispatcher  The application dispatcher object.
	 * @since  13.1
	 */
	protected $dispatcher;

	/**
	 * @var    Identity  The application identity object.
	 * @since  13.1
	 */
	protected $identity;

	/**
	 * @var    Language  The application language object.
	 * @since  13.1
	 */
	protected $language;

	/**
	 * Class constructor.
	 *
	 * @param   Provider  $provider  An optional argument to provide dependency injection for the application's
	 *                               provider object.  If the argument is a Provider object that object will become
	 *                               the application's provider object, otherwise a default provider object is created.
	 *
	 * @since   13.1
	 */
	public function __construct(Provider $provider = null)
	{
		// Setup our dependency provider.
		$this->provider = ($provider instanceof Provider) ? $provider : new Provider;

		$this->config = new Registry;

		$input = $this->provider->get('input');
		if ($input instanceof Input)
		{
			$this->input = $input;
		}
		else
		{
			$this->input = new Input($this->provider);
			$this->provider->set('input', $this->input);
		}

		$dispatcher = $this->provider->get('dispatcher');
		if ($dispatcher instanceof Dispatcher)
		{
			$this->dispatcher = $dispatcher;
		}
		else
		{
			$this->dispatcher = new Dispatcher;
			$this->provider->set('dispatcher', $this->dispatcher);
		}

		$language = $this->provider->get('language');
		if ($language instanceof Language)
		{
			$this->language = $language;
		}

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());

		// Set the application to the dependency provider.
		if (!$this->provider->has('application'))
		{
			$this->provider->set('application', $this);
		}
	}

	/**
	 * Method to close the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   13.1
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Returns a configuration property or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   13.1
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * Get the application configuration.
	 *
	 * @return  Registry  The application configuration registry.
	 *
	 * @since   13.1
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Get the application identity.
	 *
	 * @return  mixed  An Identity object or null.
	 *
	 * @since   13.1
	 */
	public function getIdentity()
	{
		return $this->identity;
	}

	/**
	 * Get the application language.
	 *
	 * @return  Language  The language object
	 *
	 * @since   13.1
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Load an object or array into the application configuration object.
	 *
	 * @param   mixed  $data  Either an array or object to be loaded into the configuration object.
	 *
	 * @return  Application  Instance of $this to allow chaining.
	 *
	 * @since   13.1
	 */
	public function loadConfiguration($data)
	{
		// Load the data into the configuration object.
		if (is_array($data))
		{
			$this->config->loadArray($data);
		}
		elseif (is_object($data))
		{
			$this->config->loadObject($data);
		}

		return $this;
	}

	/**
	 * Register an event listener.
	 *
	 * @param   object  $listener    The event listener (can be any object including a closure).
	 * @param   array   $events      An array of event names the listener wants to listen to.
	 *                               For closures, this parameter is needed.
	 *                               For other objects, if this parameter is ommited, the listeners will
	 *                               be registered to events corresponding to their method names.
	 * @param   array   $priorities  An array containing the event names as key and the corresponding
	 *                               listener priority for that event as value.
	 *
	 * @return  Application  This method is chainable.
	 *
	 * @since   13.1
	 */
	public function registerListener($listener, array $events = array(), array $priorities = array())
	{
		if ($this->dispatcher instanceof Dispatcher)
		{
			$this->dispatcher->registerListener($listener, $events, $priorities);
		}

		return $this;
	}

	/**
	 * Modifies a configuration property, creating it if it does not already exist.
	 *
	 * @param   string  $key    The name of the property.
	 * @param   mixed   $value  The value of the property to set (optional).
	 *
	 * @return  mixed   Previous value of the property
	 *
	 * @since   13.1
	 */
	public function set($key, $value = null)
	{
		$previous = $this->config->get($key);
		$this->config->set($key, $value);

		return $previous;
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @param   Event  $event  The event to trigger.
	 *
	 * @return  array   An array of results from each function call, or null if no dispatcher is defined.
	 *
	 * @since   13.1
	 */
	public function triggerEvent(Event $event)
	{
		if ($this->dispatcher instanceof Dispatcher)
		{
			return $this->dispatcher->triggerEvent($event);
		}

		return null;
	}

	/**
	 * Allows the application to load a custom or default identity.
	 *
	 * The logic and options for creating this object are adequately generic for default cases
	 * but for many applications it will make sense to override this method and create an identity,
	 * if required, based on more specific needs.
	 *
	 * @param   Identity  $identity  An optional identity object. If omitted, the factory user is created.
	 *
	 * @return  Application  This method is chainable.
	 *
	 * @since   13.1
	 */
	public function loadIdentity(Identity $identity = null)
	{
		$this->identity = $identity;

		return $this;
	}

	/**
	 * You will extend this method in child classes to provide configuration data from whatever data source is relevant
	 * for your specific application.
	 *
	 * @return  mixed   Either an array or object to be loaded into the configuration object.
	 *
	 * @since   13.1
	 */
	abstract protected function fetchConfigurationData();
}
