<?php

namespace Bex\Bbc\Plugin;

use Bitrix\Main\ArgumentTypeException;

trait PluginTrait
{
    private $usedPlugins = [];

    public function plugins()
    {
        return [];
    }

    public function executePluginsProlog()
    {
        $plugins = $this->plugins();

        if (!is_array($plugins))
        {
            throw new ArgumentTypeException('plugins', 'array');
        }

        foreach ($plugins as $plugin)
        {
            $pluginInstance = call_user_func([$plugin['class'], 'getInstance'], $this);

            if ($pluginInstance instanceof Plugin)
            {
                $this->usedPlugins[] = $pluginInstance;

                if (method_exists($pluginInstance, 'executeProlog'))
                {
                    call_user_func([$pluginInstance, 'executeProlog'], $this);
                }
            }
        }
    }

    public function executePluginsMain()
    {
        foreach ($this->usedPlugins as $plugin)
        {
            if (method_exists($plugin, 'executeMain'))
            {
                call_user_func($plugin->executeMain(), $this);
            }
        }
    }

    public function executePluginsEpilog()
    {
        foreach ($this->usedPlugins as $plugin)
        {
            if (method_exists($plugin, 'executeEpilog'))
            {
                call_user_func($plugin->executeEpilog(), $this);
            }
        }
    }

    public function executePluginsFinal()
    {
        foreach ($this->usedPlugins as $plugin)
        {
            if (method_exists($plugin, 'executeFinal'))
            {
                call_user_func($plugin->executeFinal(), $this);
            }
        }
    }
}