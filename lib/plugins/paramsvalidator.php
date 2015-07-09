<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Plugins;

use Bex\AdvancedComponent\Plugin;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\NotSupportedException;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ParamsValidatorPlugin extends Plugin
{
    /**
     * @var array List keys from $this->arParams for checking
     * @example $checkParams = ['IBLOCK_TYPE' => ['type' => 'string'], 'ELEMENT_ID' => ['type' => 'int', 'error' => '404']];
     */
    protected $checkParams = [];

    public function executeInit()
    {
        $this->checkParams();
    }

    /**
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function checkParams()
    {
        foreach ($this->checkParams as $key => $params)
        {
            $exception = false;

            switch ($params['type'])
            {
                case 'int':

                    if (!is_numeric($this->arParams[$key]) && $params['error'] !== false)
                    {
                        $exception = new ArgumentTypeException($key, 'integer');
                    }
                    else
                    {
                        $this->arParams[$key] = intval($this->arParams[$key]);
                    }

                    break;

                case 'string':

                    $this->arParams[$key] = htmlspecialchars(trim($this->arParams[$key]));

                    if (strlen($this->arParams[$key]) <= 0 && $params['error'] !== false)
                    {
                        $exception = new ArgumentNullException($key);
                    }

                    break;

                case 'array':

                    if (!is_array($this->arParams[$key]))
                    {
                        if ($params['error'] === false)
                        {
                            $this->arParams[$key] = [$this->arParams[$key]];
                        }
                        else
                        {
                            $exception = new ArgumentTypeException($key, 'array');
                        }
                    }

                    break;

                default:
                    $exception = new NotSupportedException('Not supported type of parameter for automatical checking');
                    break;
            }

            if ($exception)
            {
                if ($this->checkParams[$key]['error'] === '404')
                {
                    // todo
                    $this->return404(true, $exception);
                }
                else
                {
                    throw $exception;
                }
            }
        }
    }
}