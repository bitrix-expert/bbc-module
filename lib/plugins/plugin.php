<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

use Bex\Bbc\BasisComponent;
use Bitrix\Main\ArgumentTypeException;

/**
 * Abstract class for plugin realization.
 *
 * The plugin is object, who running in parallel with component. Component performs the role of the event manager:
 * registers the plugins and trigger their if you experience event.
 *
 * Example registering plugins on your component:
 *
 * ```php
 * use Bex\Bbc\BasisComponent;
 * use Bex\Bbc\Plugins\CatcherPlugin;
 * use Bex\Bbc\Plugins\IncluderPlugin;
 *
 * class MyComponent extends BasisComponent
 * {
 *      public function configurate()
 *      {
 *          $this->catcher = new CatcherPlugin();
 *          $this->includer = new IncluderPlugin();
 *
 *          $this->pluginManager
 *              ->register($this->catcher)
 *              ->register($this->includer);
 *      }
 * }
 * ```
 */
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
     * Initialization of the plugin
     *
     * @param BasisComponent $component Component object
     */
    final public function __construct($component)
    {
        $this->component = $component;

        $this->configurate();
    }

    /**
     * Returns the fully qualified name of this class
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
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
        return $this->component->getDispatcher()->get($plugin);
    }
}