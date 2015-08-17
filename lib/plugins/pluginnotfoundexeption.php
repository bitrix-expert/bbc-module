<?php

namespace Bex\Bbc\Plugins;

class PluginNotFoundExeption extends \Exception
{
    protected $plugin;

    /**
     * @param string $plugin
     * @param \Exception $previous
     */
    public function __construct($message, $plugin, \Exception $previous = null)
    {
        $this->plugin = $plugin;

        parent::__construct($message, 0, $previous);
    }

    public function getPlugin()
    {
        return $this->plugin;
    }
}