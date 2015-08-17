<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

use Bitrix\Main\ArgumentTypeException;
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
    private $pluginsByTypes;

    /**
     * @param BasisComponent $component
     *
     * @throws ArgumentTypeException
     */
    public function __construct($component)
    {
        $this->component = $component;

        $component->pluginManager = $this;
        $component->configurate();

        $this->sort();
    }

    /**
     * Called to notify the plugin about the happened event
     *
     * @param string $action Action name
     */
    public function trigger($action)
    {
        $this->init();

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

    private function sort()
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
     * @param Plugin $plugin Object of plugin
     *
     * @return $this
     */
    public function register($plugin)
    {
        if ($plugin instanceof Plugin)
        {
            if (isset($this->plugins[$plugin::className()]))
            {
                return $this;
            }

            $this->plugins[$plugin::className()] = $plugin;

            $plugin->init($this->component);

            foreach ($plugin->dependencies() as $dependency)
            {
                $this->register($dependency);
            }

            $this->pluginsByTypes[$plugin->getType()] = &$plugin;
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
     * @param string $plugin Class name of plugin or type of plugin
     *
     * @return Plugin
     */
    public function get($plugin)
    {
        if ($this->pluginsByTypes[$plugin])
        {
            return $this->pluginsByTypes[$plugin];
        }

        return $this->plugins[$plugin];
    }
}