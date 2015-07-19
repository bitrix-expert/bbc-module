<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc;

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
    protected $templatePage;
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

    public function configurate()
    {
        $this->errorNotifier = new ErrorNotifierPlugin();
        $this->includer = new IncluderPlugin();
        $this->paramsValidator = new ParamsValidatorPlugin();

        $this->pluginManager
            ->register($this->errorNotifier)
            ->register($this->includer)
            ->register($this->paramsValidator);
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

        $this->readUsedTraits();

        $this->startAjax();

        $this->executeTraits('prolog');
        $this->pluginManager->trigger('executeProlog');
        $this->executeProlog();

        if ($this->startCache())
        {
            $this->executeMain();
            $this->executeTraits('main');
            $this->pluginManager->trigger('executeMain');

            if ($this->cacheTemplate)
            {
                $this->render();
            }

            $this->writeCache();
        }

        if (!$this->cacheTemplate)
        {
            $this->render();
        }

        $this->executeTraits('epilog');
        $this->pluginManager->trigger('executeMain');
        $this->executeEpilog();

        $this->pluginManager->trigger('executeFinal');
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