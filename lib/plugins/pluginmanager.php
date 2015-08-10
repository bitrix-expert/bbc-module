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
    /**
     * @var array
     */
    private $plugins;

    protected $component;

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
    }

    /**
     * Called to notify the plugin about the happened event
     *
     * @param string $action Action name
     */
    public function trigger($action)
    {
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
     * @param Plugin $plugin Object of plugin
     *
     * @return $this
     */
    public function register($plugin)
    {
        if ($plugin instanceof Plugin)
        {
            $this->plugins[$plugin::className()] = $plugin;

            $plugin->init($this->component);

            foreach ($plugin->dependencies() as $dependency)
            {
                $this->register($dependency);
            }

            /**
             * @todo Register plugin type
             */
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

    public function get($pluginName)
    {
        return $this->plugins[$pluginName];
    }
}