<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc;

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
        $this->executeProlog();

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

        $this->executeEpilog();
        $this->pluginManager->trigger('executeFinal');

        $this->ajax->stop();
    }

    protected function executeProlog()
    {
    }

    protected function executeMain()
    {
    }

    protected function executeEpilog()
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