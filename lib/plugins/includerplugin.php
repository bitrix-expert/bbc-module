<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class IncluderPlugin extends Plugin
{
    /**
     * @var array The codes of modules that will be connected when performing component
     */
    private $needModules = [];

    public function beforeAction()
    {
        $this->includeModules();
    }

    /**
     * Include modules
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public function includeModules()
    {
        if (empty($this->needModules))
        {
            return false;
        }

        foreach ($this->needModules as $module)
        {
            if (!Loader::includeModule($module))
            {
                throw new LoaderException('Failed include module "' . $module . '"');
            }
        }
    }

    /**
     * @param array|string $modules
     *
     * @return $this
     * @throws ArgumentTypeException
     */
    public function addModule($modules)
    {
        if (is_string($modules))
        {
            $modules = explode(',', $modules);
        }
        elseif (!is_array($modules))
        {
            throw new ArgumentTypeException('$modules', 'string or array');
        }

        trimArr($modules);
        $this->needModules += $modules;

        return $this;
    }

    /**
     * @param array|string $modules
     *
     * @return $this
     * @throws ArgumentTypeException
     */
    public function removeModule($modules)
    {
        if (is_string($modules))
        {
            $modules = explode(',', $modules);
        }
        elseif (!is_array($modules))
        {
            throw new ArgumentTypeException('$modules', 'string or array');
        }

        trimArr($modules);
        while (($i = array_search($modules, $this->needModules)) !== false)
        {
            unset($this->needModules[$i]);
        }

        return $this;
    }
}