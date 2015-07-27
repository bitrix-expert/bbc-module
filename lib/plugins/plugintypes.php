<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
interface PluginTypes
{
    /**
     * Type of common plugins (by default)
     */
    const COMMON = 'common';
    /**
     * Type of plugins for cache
     */
    const CACHE = 'cache';
    /**
     * Type of plugins for AJAX
     */
    const AJAX = 'ajax';
}