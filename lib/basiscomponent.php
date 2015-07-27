<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc;

use Bex\Bbc\Plugins\PluginTypes;
use Bitrix\Main\Context;
use Bex\Bbc\Plugins\AjaxPlugin;
use Bex\Bbc\Plugins\CachePlugin;
use Bex\Bbc\Plugins\PluginManager;
use Bex\Bbc\Plugins\IncluderPlugin;
use Bex\Bbc\Plugins\CatcherPlugin;
use Bex\Bbc\Plugins\ParamsValidatorPlugin;

/**
 * Abstraction basis component
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
abstract class BasisComponent extends \CBitrixComponent
{
    /**
     * @var string Default action name
     */
    protected $defaultAction = 'list';
    /**
     * @var string Name of action
     */
    public $action;
    /**
     * @var PluginManager
     */
    public $pluginManager;
    /**
     * @var CatcherPlugin
     */
    public $errorNotifier;
    /**
     * @var IncluderPlugin
     */
    public $includer;
    /**
     * @var ParamsValidatorPlugin
     */
    public $paramsValidator;
    /**
     * @var AjaxPlugin
     */
    public $ajax;
    /**
     * @var CachePlugin
     */
    public $cache;

    public function configurate()
    {
        $this->errorNotifier = new CatcherPlugin();
        $this->includer = new IncluderPlugin();
        $this->paramsValidator = new ParamsValidatorPlugin();
        $this->ajax = new AjaxPlugin();
        $this->cache = new CachePlugin();

        $this->pluginManager
            ->register($this->cache, PluginTypes::CACHE)
            ->register($this->ajax, PluginTypes::AJAX)
            ->register($this->errorNotifier)
            ->register($this->includer)
            ->register($this->paramsValidator);
        /**
         * @todo Мб читать свойства класса?
         */
    }

    public function routes()
    {
        return [];
    }

    public function getRouteVariables()
    {
        return [];
    }

    /**
     * Is search route
     *
     * @return bool
     */
    public function isSearchRoute()
    {
        $request = Context::getCurrent()->getRequest();

        if (strlen($request->get('q')) > 0)
        {
            return true;
        }

        return false;
    }

    protected function readRoute()
    {
        if ($this->arParams['SEF_MODE'] === 'Y')
        {
            $variables = [];

            $urlTemplates = \CComponentEngine::MakeComponentUrlTemplates(
                $this->routes(),
                $this->arParams['SEF_URL_TEMPLATES']
            );

            $variableAliases = \CComponentEngine::MakeComponentVariableAliases(
                $this->routes(),
                $this->arParams['VARIABLE_ALIASES']
            );

            $this->action = \CComponentEngine::ParseComponentPath(
                $this->arParams['SEF_FOLDER'],
                $urlTemplates,
                $variables
            );

            if (!$this->action)
            {
                if ($this->arParams['SET_404'] === 'Y')
                {
                    $folder404 = str_replace('\\', '/', $this->arParams['SEF_FOLDER']);

                    if ($folder404 != '/')
                    {
                        $folder404 = '/'.trim($folder404, "/ \t\n\r\0\x0B")."/";
                    }

                    if (substr($folder404, -1) == '/')
                    {
                        $folder404 .= 'index.php';
                    }

                    if ($folder404 != Context::getCurrent()->getRequest()->getRequestedPage())
                    {
                        $this->return404();
                    }
                }

                $this->action = $this->defaultAction;
            }

            if ($this->isSearchRoute() && $this->arParams['USE_SEARCH'] === 'Y')
            {
                $this->action = 'search';
            }

            \CComponentEngine::InitComponentVariables(
                $this->action,
                $this->getRouteVariables(),
                $variableAliases,
                $variables
            );

            $this->setRoutesResult($this->arParams['SEF_FOLDER'], $urlTemplates, $variables, $variableAliases);
        }
        else
        {
            $this->action = $this->defaultAction;
        }
    }

    protected function setRoutesResult($sefFolder, $urlTemplates, $variables, $variableAliases)
    {
        $this->arResult['FOLDER'] = $sefFolder;
        $this->arResult['URL_TEMPLATES'] = $urlTemplates;
        $this->arResult['VARIABLES'] = $variables;
        $this->arResult['ALIASES'] = $variableAliases;
    }

    /**
     * Set status 404 and throw exception
     *
     * @todo 404-я должна выкидываться исключением
     *
     * @param bool $notifier Sent notify to admin email
     * @param \Exception|null|false $exception Exception which will be throwing or "false" what not throwing exceptions. Default — throw new \Exception()
     * @throws \Exception
     */
    public function return404($notifier = false, \Exception $exception = null)
    {
        @define('ERROR_404', 'Y');
        \CHTTP::SetStatus('404 Not Found'); // todo Replace on D7

        if ($exception !== false)
        {
            if ($notifier === false)
            {
                $this->exceptionNotifier = false;
            }

            if ($exception instanceof \Exception)
            {
                throw $exception;
            }
            else
            {
                throw new \Exception('Page not found');
            }
        }
    }

    final protected function executeBasis()
    {
        $this->pluginManager = new PluginManager($this);

        $this->pluginManager->trigger('executeInit');

        $this->ajax->start();

        $this->pluginManager->trigger('beforeAction');
        $this->beforeAction();

        $this->executeAction();
        $this->pluginManager->trigger('action');

        $this->afterAction();
        $this->pluginManager->trigger('afterAction');

        $this->ajax->stop();
    }

    /**
     * Invoked before the action component
     */
    protected function beforeAction()
    {
    }

    protected function executeAction()
    {
        $this->readRoute();

        $actionMethod = $this->action . 'Action';

        if (method_exists($this, $actionMethod))
        {
            $this->{$actionMethod}();
        }
        else
        {
            // @todo 404
            throw new \Exception('not found');
        }
    }

    /**
     * Invoked after the action component
     */
    protected function afterAction()
    {
    }

    public function executeComponent()
    {
        try {
            $this->executeBasis();
        } catch (\Exception $e) {
            $this->errorNotifier->catchException($e);
        }
    }
}