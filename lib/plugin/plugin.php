<?php

namespace Bex\Bbc\Plugin;

use Bitrix\Main\ArgumentTypeException;

abstract class Plugin
{
    /**
     * @var static
     */
    protected static $instance = null;

    /**
     * @var \CBitrixComponent
     */
    protected $component;

    protected function __construct($component)
    {
        $this->component = $component;
    }

    /**
     * @return static
     *
     * @throws ArgumentTypeException
     */
    public static function getInstance()
    {
        if (!isset(static::$instance))
        {
            $component = func_get_arg(0);

            if (!$component instanceof \CBitrixComponent)
            {
                throw new ArgumentTypeException('$component', '\CBitrixComponent');
            }

            static::$instance = new static($component);
        }

        return static::$instance;
    }

    public static function getClass()
    {
        return get_called_class();
    }

    public function dependencies()
    {

    }
}