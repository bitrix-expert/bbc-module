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
interface AjaxInterface
{
    /**
     * Restart buffer if AJAX request
     */
    public function start();

    /**
     * Stop execute script if AJAX request
     */
    public function stop();

    /**
     * Is AJAX request
     *
     * @return bool
     */
    public function isAjax();
}