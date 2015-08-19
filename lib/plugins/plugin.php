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

    public function init(\CBitrixComponent $component)
    {
        $this->component = $component;
    }

    public function setSort($sort)
    {

    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setInterface($interface)
    {
        if (!is_string($interface))
        {
            throw new ArgumentTypeException('Type of the plugin must be string');
        }

        $this->interface = $interface;
    }

    public function getInterface()
    {
        return $this->interface;
    }

    public function dependencies()
    {
        return [];
    }

    protected function getDependency($pluginName)
    {

    }

    /**
     * Gets the plugin object that implements one of the interfaces
     *
     * @param string $interface Type of plugin. Use constants of \Bex\Bbc\Plugins\PluginTypes
     *
     * @return Plugin
     *
     * @throws PluginNotFoundException
     */
    protected function getImplementsPlugin($interface)
    {
        return $this->pluginManager->getByInterface($interface);
    }
}