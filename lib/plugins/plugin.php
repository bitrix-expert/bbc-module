<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

use Bex\Bbc\BasisComponent;

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
    private $pluginSort = 100;
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
    public function setPluginSort($sort)
    {
        $this->pluginSort = intval($sort);
    }

    /**
     * Gets the sorting plugin
     *
     * @return int
     */
    public function getPluginSort()
    {
        return $this->pluginSort;
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