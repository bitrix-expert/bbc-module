<?php

namespace Bex\Bbc\Plugins;

use Bex\Bbc\BasisComponent;

abstract class Plugin
{
    /**
     * @var BasisComponent
     */
    protected $component;
    /**
     * @var int Sorting plugin
     * @todo Внедрить сортировку в плагины
     */
    public $sort = 100;

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

    protected function getDependency($dependency)
    {
        return new CachePlugin();
    }
}