<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\AdvancedComponent;

use Bitrix\Main\ArgumentTypeException;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class PluginManager
{
    private $usedPlugins = [];

    /**
     * @param \CBitrixComponent|AdvancedComponentTrait $component
     *
     * @throws ArgumentTypeException
     */
    public function __construct($component)
    {
        $plugins = $component->plugins();

        if (!is_array($plugins))
        {
            throw new ArgumentTypeException('plugins', 'array');
        }
        elseif (!empty($plugins))
        {
            foreach ($plugins as $plugin)
            {
                if (is_array($plugin) && isset($plugin['class']))
                {
                    $class = $plugin['class'];
                }
                elseif (is_string($plugin) && strlen($plugin) > 0)
                {
                    $class = $plugin;
                }
                else
                {
                    continue;
                }

                $pluginInstance = call_user_func_array([$class, 'getInstance'], [$component]);

                if ($pluginInstance instanceof Plugin)
                {
                    $this->usedPlugins[] = $pluginInstance;
                }
                else
                {
                    /**
                     * @todo Return notice
                     */
                }
            }
        }
    }

    public function trigger($action)
    {
        if ($action && !empty($this->usedPlugins))
        {
            foreach ($this->usedPlugins as $plugin)
            {
                if (method_exists($plugin, $action))
                {
                    call_user_func($plugin->$action());
                }
            }
        }
    }

    public function add()
    {

    }

    public function remove()
    {

    }

    public function getList()
    {
        return $this->usedPlugins;
    }
}