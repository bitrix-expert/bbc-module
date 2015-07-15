<?php

namespace Bex\Bbc\Plugins;

use Bitrix\Main\ArgumentTypeException;

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

    /*public function __construct()
    {
        $component = func_get_arg(0);

        if (!$component instanceof \CBitrixComponent)
        {
            throw new ArgumentTypeException('$component', '\CBitrixComponent');
        }

        $this->component = $component;
    }*/

    /**
     * @return static
     *
     * @throws ArgumentTypeException
     */
    /*public static function getInstance()
    {
        if (!isset(static::$instance[get_called_class()]))
        {
            $component = func_get_arg(0);

            if (!$component instanceof \CBitrixComponent)
            {
                throw new ArgumentTypeException('$component', '\CBitrixComponent');
            }

            static::$instance[get_called_class()] = new static($component);
        }

        return static::$instance[get_called_class()];
    }*/

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