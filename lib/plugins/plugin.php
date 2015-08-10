<?php

namespace Bex\Bbc\Plugins;

use Bex\Bbc\BasisComponent;
use Bitrix\Main\ArgumentTypeException;

abstract class Plugin
{
    /**
     * @var BasisComponent
     */
    protected $component;
    /**
     * @var int Sorting plugin. Determines the order of plugins execution
     * @todo Внедрить сортировку в плагины
     */
    private $sort = 100;
    /**
     * @var string Type of plugin. Takes the value of the \Bex\Bbc\Plugins\PluginTypes interface
     */
    private $type = PluginTypes::COMMON;

    final public function __construct()
    {
    }

    public static function className()
    {
        return get_called_class();
    }

    public function init(\CBitrixComponent $component)
    {
        $this->component = $component;
    }

    public function setSort($sort)
    {

    }

    public function getSort()
    {
        return $this->sort;
    }

    public function setType($type)
    {
        if (!is_string($type))
        {
            throw new ArgumentTypeException('Type of the plugin must be string');
        }

        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function dependencies()
    {
        return [];
    }

    protected function getDependency($pluginName)
    {
        return new CachePlugin();
    }
}