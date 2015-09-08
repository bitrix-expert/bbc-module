<?php

namespace Bex\Bbc\Plugins;

class PluginNotRegisteredException extends \Exception
{
    protected $plugin;

    /**
     * @param string $plugin
     * @param \Exception $previous
     */
    public function __construct($plugin, \Exception $previous = null)
    {
        $this->plugin = $plugin;

        parent::__construct('Plugin ' . $plugin . ' is not defined', 0, $previous);
    }

    public function getPlugin()
    {
        return $this->plugin;
    }
}