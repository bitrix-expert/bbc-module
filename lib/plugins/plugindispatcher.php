<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

use Bex\Bbc\BasisComponent;

/**
 * Dispatcher plugins of component.
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class PluginDispatcher
{
    protected $component;
    private $plugins;
    private $isFirstTrigger = true;

    /**
     * @param BasisComponent $component
     *
     * @return PluginDispatcher
     */
    public function __construct(BasisComponent $component)
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
        if ($this->isFirstTrigger === true) {
            $this->loadConfiguration();
            $this->isFirstTrigger = false;
        }

        if (!empty($this->plugins)) {
            foreach ($this->plugins as $plugin) {
                if (method_exists($plugin, $action)) {
                    $plugin->$action();
                }
            }
        }
    }

    protected function loadConfiguration()
    {
        $plugins = $this->component->plugins();

        if (!is_array($plugins) || empty($plugins)) {
            return;
        }

        foreach ($plugins as $name => $className) {
            $this->register($name, $className);
        }

// @todo сортировка должна сохранять ключи
//        usort($this->plugins, function($previous, $next) {
//            /**
//             * @var Plugin $previous
//             * @var Plugin $next
//             */
//
//            if ($previous->getSort() === $next->getSort())
//            {
//                return 0;
//            }
//            elseif ($previous->getSort() < $next->getSort())
//            {
//                return -1;
//            }
//            else
//            {
//                return 1;
//            }
//        });
    }

    /**
     * @param string $name Plugin name.
     * @param string $className Plugin class name instanceof `\Bex\Bbc\Plugins\Plugin`.
     *
     * @return $this
     */
    public function register($name, $className)
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Plugin "' . $name . '" not found');
        } elseif (is_subclass_of($className, Plugin::className())) {
            if (isset($this->plugins[$name])) {
                return $this;
            }

            /**
             * @var Plugin $plugin
             */
            $plugin = new $className($this->component);

            $this->plugins[$name] = $plugin;

            foreach ($plugin->dependencies() as $dependency) {
                $this->register($dependency);
            }
        } else {
            throw new \InvalidArgumentException('Plugin "' . $name . '" isn\'t instanceof \Bex\Bbc\Plugins\Plugin');
        }

        return $this;
    }

    /**
     * Delete plugin from component
     *
     * @param string $name Name of class for delete plugin. Can be used method getClass: PluginClass::getClass()
     *
     * @return $this
     */
    public function remove($name)
    {
        unset($this->plugins[$name]);

        return $this;
    }

    /**
     * Get plugin object by his name.
     *
     * For example:
     *
     * ```php
     * // by class name
     * use Bex\Bbc\Plugins\IncluderPlugin;
     *
     * $plugin = $this->pluginManager->get(IncluderPlugin::className());
     * ```
     *
     * @param string $name Name of plugin.
     *
     * @return Plugin|null
     *
     * @throws PluginNotRegisteredException If plugin is not registered.
     */
    public function get($name)
    {
        if (isset($this->plugins[$name])) {
            return $this->plugins[$name];
        } else {
            throw new PluginNotRegisteredException($name);
        }
    }
}