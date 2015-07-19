<?php

namespace Bex\Bbc\Plugins;

abstract class Plugin
{
    /**
     * @var static
     */
    protected static $instance = [];
    /**
     * @var \CBitrixComponent
     */
    protected $component;

    public static function getName()
    {
        return get_called_class();
    }

    public function init(\CBitrixComponent $component)
    {
        $this->component = $component;
    }

    public function dependencies()
    {

    }
}