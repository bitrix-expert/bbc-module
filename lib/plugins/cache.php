<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

use Bitrix\Main\Application;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class CachePlugin extends Plugin
{
    /**
     * @var array Additional cache ID
     */
    private $additionalId;
    /**
     * @var string Cache dir
     */
    public $cacheDir = false;

    /**
     * Cache init
     *
     * @return bool
     */
    public function start()
    {
        global $USER;

        if ($this->component->arParams['CACHE_TYPE'] && $this->component->arParams['CACHE_TYPE'] !== 'N'
            && $this->component->arParams['CACHE_TIME'] > 0)
        {
            if ($this->component->action)
            {
                $this->additionalId[] = $this->component->action;
            }

            if ($this->component->arParams['CACHE_GROUPS'] === 'Y')
            {
                $this->additionalId[] = $USER->GetGroups();
            }

            if ($this->component->startResultCache($this->component->arParams['CACHE_TIME'], $this->additionalId, $this->cacheDir))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Write cache to disk
     */
    public function stop()
    {
        $this->component->endResultCache();
    }

    /**
     * Resets the cache
     */
    public function abort()
    {
        $this->component->abortResultCache();
    }

    /**
     * Register tag in cache
     *
     * @param string $tag Tag
     */
    public static function registerTag($tag)
    {
        if ($tag)
        {
            Application::getInstance()->getTaggedCache()->registerTag($tag);
        }
    }

    /**
     * Add additional ID to cache
     *
     * @param mixed $id
     */
    public function addId($id)
    {
        $this->additionalId[] = $id;
    }
}