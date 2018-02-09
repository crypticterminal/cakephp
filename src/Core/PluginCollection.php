<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.6.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Core;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use RuntimeException;

/**
 * Plugin Collection
 *
 * Holds onto plugin objects loaded into an application, and
 * provides methods for iterating, and finding plugins based
 * on criteria.
 */
class PluginCollection implements IteratorAggregate, Countable
{
    /**
     * Plugin list
     *
     * @var array
     */
    protected $plugins = [];

    const VALID_HOOKS = ['routes', 'bootstrap', 'console', 'middleware'];

    /**
     * Constructor
     *
     * @param array $plugins The map of plugins to add to the collection.
     */
    public function __construct(array $plugins = [])
    {
        foreach ($plugins as $plugin) {
            $this->add($plugin);
        }
    }

    /**
     * Add a plugin to the collection
     *
     * Plugins will be keyed by their names.
     *
     * @param \Cake\Core\PluginInterface $plugin The plugin to load.
     * @return $this
     */
    public function add(PluginInterface $plugin)
    {
        $name = $plugin->getName();
        $this->plugins[$name] = $plugin;

        return $this;
    }

    /**
     * Remove a plugin from the collection if it exists.
     *
     * @param string $name The named plugin.
     * @return $this
     */
    public function remove($name)
    {
        unset($this->plugins[$name]);

        return $this;
    }

    /**
     * Check whether the named plugin exists in the collection.
     *
     * @param string $name The named plugin.
     * @return bool
     */
    public function has($name)
    {
        return isset($this->plugins[$name]);
    }

    /**
     * Get the a plugin by name
     *
     * @param string $name The plugin to get.
     * @return \Cake\Core\PluginInterface The plugin.
     * @throws \InvalidArgumentException when unknown plugins are fetched.
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException("`$name` is not a loaded plugin.");
        }

        return $this->plugins[$name];
    }

    /**
     * Implementation of IteratorAggregate.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->plugins);
    }

    /**
     * Implementation of Countable.
     *
     * Get the number of plugins in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->plugins);
    }

    /**
     * Filter the plugins to those with the named hook enabled.
     *
     * @param string $hook The hook to filter plugins by
     * @return \Generator A generator containing matching plugins.
     * @throws \InvalidArgumentException on invalid hooks
     */
    public function with($hook)
    {
        if (!in_array($hook, static::VALID_HOOKS)) {
            throw new InvalidArgumentException("The `{$hook}` hook is not a known plugin hook.");
        }
        $hook = ucfirst($hook);
        $method = "is{$hook}Enabled";
        foreach ($this as $plugin) {
            if ($plugin->{$method}()) {
                yield $plugin;
            }
        }
    }
}
