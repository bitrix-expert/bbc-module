<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

use Bitrix\Main\ArgumentTypeException;

/**
 * Plugin Manager
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class PluginManager
{
    /**
     * @var Plugin
     */
    private $pluginsInstance;

    protected $component;

    /**
     * @param \CBitrixComponent|AdvancedComponentTrait $component
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
        if (!empty($this->pluginsInstance))
        {
            foreach ($this->pluginsInstance as $instance)
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
    public function add($plugin)
    {
        $pluginInstance = call_user_func_array([$plugin, 'getInstance'], [$this->component]);

        if ($pluginInstance instanceof Plugin)
        {
            $this->pluginsInstance[$plugin] = $pluginInstance;
        }
        else
        {
            throw new \InvalidArgumentException('Plugin not instanceof Plugin');
        }

        return $this;
    }

    /**
     * Delete plugin from component
     *
     * @param string $class Name of class for delete plugin. Can be used method getClass: PluginClass::getClass()
     *
     * @return $this
     */
    public function remove($class)
    {
        unset($this->pluginsInstance[$class]);

        return $this;
    }

    public function get($class)
    {
        return $this->pluginsInstance[$class];
    }
}