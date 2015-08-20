<?php

namespace Bex\Bbc\Plugins;

use Bex\Bbc\BasisComponent;
use Bitrix\Main\ArgumentTypeException;

abstract class Plugin
{
    /**
     * @var int Sorting plugin. Determines the order of plugins execution
     */
    private $sort = 100;
    /**
     * @var string Type interface of plugin. Takes the value of the \Bex\Bbc\Plugins\PluginInterface
     */
    private $interface = PluginInterface::TYPICAL;
    /**
     * @var BasisComponent
     */
    protected $component;
    /**
     * @var PluginManager
     */
    private $pluginManager;

    final public function __construct()
    {
    }

    public static function className()
    {
        return get_called_class();
    }

    /**
     * Initialization of the plugin
     *
     * @param BasisComponent $component Component object
     */
    final public function init(BasisComponent $component)
    {
        $this->component = $component;

        $this->configurate();
    }

    /**
     * Configuration plugin. Method for configurate your plugin before his work
     */
    public function configurate()
    {
    }

    /**
     * Set sorting of plugin
     *
     * @param int $sort
     */
    public function setSort($sort)
    {
        $this->sort = intval($sort);
    }

    /**
     * Gets the sorting plugin
     *
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Sets interface for the plugin
     *
     * @param string $interface Interface of plugin. One of constant \Bex\Bbc\Plugins\PluginInterface
     *
     * @throws ArgumentTypeException If $interface not string
     */
    public function setInterface($interface)
    {
        if (!is_string($interface))
        {
            throw new ArgumentTypeException('Type of the plugin must be string');
        }

        $this->interface = $interface;
    }

    /**
     * Gets current interface of the plugin
     *
     * @return string
     */
    public function getInterface()
    {
        return $this->interface;
    }

    public function dependencies()
    {
        return [];
    }

    /**
     * Gets the plugin object
     *
     * @param string $plugin Class name or type of plugin (use constants of \Bex\Bbc\Plugins\PluginTypes)
     *
     * @return Plugin|null
     */
    protected function getPlugin($plugin)
    {
        return $this->component->pluginManager->get($plugin);
    }
}