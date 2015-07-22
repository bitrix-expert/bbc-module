<?php

namespace Bex\Bbc\Plugins;

use Bex\Bbc\BasisComponent;

abstract class Plugin
{
    /**
     * @var static
     */
    protected static $instance = [];
    /**
     * @var BasisComponent
     */
    protected $component;

    final public function __construct()
    {
    }

    public static function getClass()
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