<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\AdvancedComponent;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;

/**
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
        $component->configurate();
    }

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

    public function add($plugin)
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
            throw new ArgumentException('invalid plugin', $plugin);
        }

        $pluginInstance = call_user_func_array([$class, 'getInstance'], [$this->component]);

        if ($pluginInstance instanceof Plugin)
        {
            $this->pluginsInstance[$class] = $pluginInstance;
        }
        else
        {
            throw new \InvalidArgumentException('Plugin not instanceof Plugin');
        }
    }

    public function remove($class)
    {
        unset($this->pluginsInstance[$class]);
    }

    public function get($class)
    {
        return $this->pluginsInstance[$class];
    }
}