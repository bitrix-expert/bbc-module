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
interface PluginInterface
{
    /**
     * Type interface of typical plugins (by default)
     */
    const TYPICAL = 'typical';
    /**
     * Type interface of plugins for cache
     */
    const CACHE = 'cache';
    /**
     * Type interface of plugins for AJAX
     */
    const AJAX = 'ajax';
}