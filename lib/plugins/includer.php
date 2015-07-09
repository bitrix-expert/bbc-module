<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Plugins;

use Bex\AdvancedComponent\Plugin;
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
    protected $needModules = [];

    public function executeInit()
    {
        $this->includeModules();
    }

    /**
     * Include modules
     *
     * @uses $this->needModules
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
}