<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
interface CacheInterface
{
    /**
     * Cache init
     *
     * @return bool
     */
    public function start();

    /**
     * Resets the cache
     */
    public function abort();

    /**
     * Write cache to disk
     */
    public function stop();

    /**
     * Add additional ID to cache
     *
     * @param mixed $id
     */
    public function addId($id);
}