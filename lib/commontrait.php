<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Common main trait for all basis components
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
trait CommonTrait
{
    /**
     * @var array Additional cache ID
     */
    private $cacheAdditionalId;
    /**
     * @var string Cache dir
     */
    protected $cacheDir = false;
    /**
     * @var bool Caching template of the component (default not cache)
     */
    protected $cacheTemplate = true;
    /**
     * @var string Salt for component ID for AJAX request
     */
    protected $ajaxComponentIdSalt;
    /**
     * @var string Template page name
     */
    protected $templatePage;

    /**
     * Checking required component params
     */
    protected function checkParams()
    {

    }

    /**
     * Restart buffer if AJAX request
     */
    private function startAjax()
    {
        if ($this->arParams['USE_AJAX'] !== 'Y')
        {
            return false;
        }

        if (strlen($this->arParams['AJAX_PARAM_NAME']) <= 0)
        {
            $this->arParams['AJAX_PARAM_NAME'] = 'compid';
        }

        if (strlen($this->arParams['AJAX_COMPONENT_ID']) <= 0)
        {
            $this->arParams['AJAX_COMPONENT_ID'] = \CAjax::GetComponentID($this->getName(), $this->getTemplateName(), $this->ajaxComponentIdSalt);
        }

        if ($this->isAjax())
        {
            global $APPLICATION;

            if ($this->arParams['AJAX_HEAD_RELOAD'] === 'Y')
            {
                $APPLICATION->ShowAjaxHead();
            }
            else
            {
                $APPLICATION->RestartBuffer();
            }

            if ($this->arParams['AJAX_TYPE'] === 'JSON')
            {
                header('Content-Type: application/json');
            }

            if (strlen($this->arParams['AJAX_TEMPLATE_PAGE']) > 0)
            {
                $this->templatePage = basename($this->arParams['AJAX_TEMPLATE_PAGE']);
            }
        }
    }

    /**
     * Cache init
     *
     * @return bool
     */
    public function startCache()
    {
        global $USER;

        if ($this->arParams['CACHE_TYPE'] && $this->arParams['CACHE_TYPE'] !== 'N' && $this->arParams['CACHE_TIME'] > 0)
        {
            if ($this->templatePage)
            {
                $this->cacheAdditionalId[] = $this->templatePage;
            }

            if ($this->arParams['CACHE_GROUPS'] === 'Y')
            {
                $this->cacheAdditionalId[] = $USER->GetGroups();
            }

            if ($this->startResultCache($this->arParams['CACHE_TIME'], $this->cacheAdditionalId, $this->cacheDir))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Write cache to disk
     */
    public function writeCache()
    {
        $this->endResultCache();
    }

    /**
     * Resets the cache
     */
    public function abortCache()
    {
        $this->abortResultCache();
    }

    protected function executeMainCommon()
    {
        if (strlen($this->arParams['AJAX_PARAM_NAME']) > 0 && strlen($this->arParams['AJAX_COMPONENT_ID']) > 0)
        {
            $this->arResult['AJAX_REQUEST_PARAMS'] = $this->arParams['AJAX_PARAM_NAME'] . '=' . $this->arParams['AJAX_COMPONENT_ID'];

            $this->setResultCacheKeys(['AJAX_REQUEST_PARAMS']);
        }
    }

    /**
     * Stop execute script if AJAX request
     */
    private function stopAjax()
    {
        if ($this->isAjax() && $this->arParams['USE_AJAX'] === 'Y')
        {
            exit;
        }
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

    /**
     * Is AJAX request
     *
     * @return bool
     */
    public function isAjax()
    {
        if (
            strlen($this->arParams['AJAX_COMPONENT_ID']) > 0
            && strlen($this->arParams['AJAX_PARAM_NAME']) > 0
            && $_REQUEST[$this->arParams['AJAX_PARAM_NAME']] === $this->arParams['AJAX_COMPONENT_ID']
            && isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
        {
            return true;
        }

        return false;
    }

    /**
     * Register tag in cache
     *
     * @param string $tag Tag
     */
    public static function registerCacheTag($tag)
    {
        if ($tag)
        {
            Application::getInstance()->getTaggedCache()->registerTag($tag);
        }
    }

    /**
     * Add additional ID to cache
     *
     * @param mixed $id
     */
    public function addCacheAdditionalId($id)
    {
        $this->cacheAdditionalId[] = $id;
    }
}