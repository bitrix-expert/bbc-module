<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\NotSupportedException;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ParamsValidatorPlugin extends Plugin
{
    /**
     * @var array List keys from $this->component->arParams for checking
     * @example $checkParams = ['IBLOCK_TYPE' => ['type' => 'string'], 'ELEMENT_ID' => ['type' => 'int', 'error' => '404']];
     */
    protected $checkParams = [];

    public function beforeAction()
    {
        $this->checkParams();
    }

    /**
     * @throws ArgumentNullException
     */
    public function checkParams()
    {
        foreach ($this->checkParams as $key => $params)
        {
            $exception = false;

            switch ($params['type'])
            {
                case 'int':

                    if (!is_numeric($this->component->arParams[$key]) && $params['error'] !== false)
                    {
                        $exception = new ArgumentTypeException($key, 'integer');
                    }
                    else
                    {
                        $this->component->arParams[$key] = intval($this->component->arParams[$key]);
                    }

                    break;

                case 'string':

                    $this->component->arParams[$key] = htmlspecialchars(trim($this->component->arParams[$key]));

                    if (strlen($this->component->arParams[$key]) <= 0 && $params['error'] !== false)
                    {
                        $exception = new ArgumentNullException($key);
                    }

                    break;

                case 'array':

                    if (!is_array($this->component->arParams[$key]))
                    {
                        if ($params['error'] === false)
                        {
                            $this->component->arParams[$key] = [$this->component->arParams[$key]];
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

    /**
     * @todo
     */
    public function add(array $parameters)
    {
        $this->checkParams += $parameters;
    }

    /**
     * @todo
     */
    public function remove()
    {

    }
}