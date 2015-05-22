<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Common;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class Params
{
    /**
     * @var \CBitrixComponent
     */
    protected $component;

    public function __construct($component)
    {
        $this->component = $component;
    }

    public function addChecks($params)
    {

    }

    public function removeChecks($params)
    {

    }
}