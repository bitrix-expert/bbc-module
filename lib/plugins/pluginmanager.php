<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

use Bex\Bbc\BasisComponent;

/**
 * Plugin Manager
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class PluginManager
{
    protected $component;
    /**
     * @var array
     */
    private $plugins;
    /**
     * @var array
     */
    private $pluginsByInterface;

    private $isFirstTrigger = false;

    /**
     * @param BasisComponent $component
     *
     * @return PluginManager
     */
    public function __construct($component)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Called to notify the plugin about the happened event
     *
     * @param string $action Action name
     */
    public function trigger($action)
    {
        if ($this->isFirstTrigger === true)
        {
            $this->prepare();
            $this->isFirstTrigger = false;
        }

        if (!empty($this->plugins))
        {
            foreach ($this->plugins as $instance)
            {
                if (method_exists($instance, $action))
                {
                    call_user_func($instance->$action());
                }
            }
        }
    }

    /**
     * Preparatory operations after register of plugins
     */
    private function prepare()
    {
        usort($this->plugins, function($previous, $next) {
            /**
             * @var Plugin $previous
             * @var Plugin $next
             */

            if ($previous->getSort() === $next->getSort())
            {
                return 0;
            }
            elseif ($previous->getSort() < $next->getSort())
            {
                return -1;
            }
            else
            {
                return 1;
            }
        });
    }

    /**
     * @param string $className Plugin class name instanceof \Bex\Bbc\Plugins\Plugin
     *
     * @return $this
     */
    public function register($className)
    {
        if ($className instanceof Plugin)
        {
            if (isset($this->plugins[$className]))
            {
                return $this;
            }

            $plugin = new $className($this->component);

            $this->plugins[$plugin] = $plugin;

            foreach ($plugin->dependencies() as $dependency)
            {
                $this->register($dependency);
            }

            $this->pluginsByInterface[$plugin->getInterface()] = &$plugin;
        }
        else
        {
            throw new \InvalidArgumentException('Plugin not instanceof \Bex\Bbc\Plugins\Plugin');
        }

        return $this;
    }

    /**
     * Delete plugin from component
     *
     * @param string $pluginName Name of class for delete plugin. Can be used method getClass: PluginClass::getClass()
     *
     * @return $this
     */
    public function remove($pluginName)
    {
        unset($this->plugins[$pluginName]);

        return $this;
    }

    /**
     * Get plugin object by his name or interface.
     *
     * For example:
     *
     * ```php
     * // by interface
     * use Bex\Bbc\Plugins\PluginInterface;
     *
     * $plugin = $this->pluginManager->get(PluginInterface::CACHE);
     *
     * // by class name
     * use Bex\Bbc\Plugins\IncluderPlugin;
     *
     * $plugin = $this->pluginManager->get(IncluderPlugin::className());
     * ```
     *
     * @param string $plugin Name or interface of plugin
     *
     * @return Plugin|null
     */
    public function get($plugin)
    {
        if (isset($this->pluginsByInterface[$plugin]))
        {
            return $this->pluginsByInterface[$plugin];
        }
        elseif (isset($this->plugins[$plugin]))
        {
            return $this->plugins[$plugin];
        }
        else
        {
            return null;
        }
    }
}