<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc;

use Bitrix\Main\Context;
use Bex\Bbc\Plugins\AjaxPlugin;
use Bex\Bbc\Plugins\CachePlugin;
use Bex\Bbc\Plugins\PluginManager;
use Bex\Bbc\Plugins\IncluderPlugin;
use Bex\Bbc\Plugins\ErrorNotifierPlugin;
use Bex\Bbc\Plugins\ParamsValidatorPlugin;

/**
 * Abstraction basis component
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
abstract class BasisComponent extends \CBitrixComponent
{
    /**
     * @var string Template page default
     */
    protected $defaultPage = 'list';
    /**
     * @var string Template page default for SEF mode
     */
    protected $defaultSefPage = 'list';
    /**
     * @var bool Caching template of the component (default not cache)
     */
    protected $cacheTemplate = true;
    /**
     * @var string Template page name
     */
    public $templatePage;
    /**
     * @var PluginManager
     */
    public $pluginManager;
    /**
     * @var ErrorNotifierPlugin
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
        $this->errorNotifier = new ErrorNotifierPlugin();
        $this->includer = new IncluderPlugin();
        $this->paramsValidator = new ParamsValidatorPlugin();
        $this->ajax = new AjaxPlugin();
        $this->cache = new CachePlugin();

        $this->pluginManager
            ->register($this->errorNotifier)
            ->register($this->includer)
            ->register($this->paramsValidator)
            ->register($this->ajax)
            ->register($this->cache);
    }

    public function routers()
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
    protected function isSearchRoute()
    {
        if (strlen($_GET['q']) > 0 && $this->templatePage !== 'detail')
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
                $this->routers(),
                $this->arParams['SEF_URL_TEMPLATES']
            );

            $variableAliases = \CComponentEngine::MakeComponentVariableAliases(
                $this->routers(),
                $this->arParams['VARIABLE_ALIASES']
            );

            $this->templatePage = \CComponentEngine::ParseComponentPath(
                $this->arParams['SEF_FOLDER'],
                $urlTemplates,
                $variables
            );

            if (!$this->templatePage)
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

                $this->templatePage = $this->defaultSefPage;
            }

            if ($this->isSearchRoute() && $this->arParams['USE_SEARCH'] === 'Y')
            {
                $this->templatePage = 'search';
            }

            \CComponentEngine::InitComponentVariables(
                $this->templatePage,
                $this->getRouteVariables(),
                $variableAliases,
                $variables
            );

            $this->setRoutesResult($this->arParams['SEF_FOLDER'], $urlTemplates, $variables, $variableAliases);
        }
        else
        {
            $this->templatePage = $this->defaultPage;
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
     * Show results. Default: include template of the component
     *
     * @uses $this->templatePage
     */
    public function render()
    {
        $this->includeComponentTemplate($this->templatePage);
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

        $this->pluginManager->trigger('executeProlog');
        $this->beforeAction();

        if ($this->cache->start())
        {
            $this->executeMain();
            $this->pluginManager->trigger('executeMain');

            if ($this->cacheTemplate)
            {
                $this->render();
            }

            $this->cache->stop();
        }

        if (!$this->cacheTemplate)
        {
            $this->render();
        }

        $this->afterAction();
        $this->pluginManager->trigger('executeFinal');

        $this->ajax->stop();
    }

    /**
     * Invoked before the action component
     */
    protected function beforeAction()
    {
    }

    protected function executeMain()
    {
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