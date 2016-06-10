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
class AjaxPlugin extends Plugin implements AjaxInterface
{
    /**
     * @var string Salt for component ID for AJAX request
     */
    protected $ajaxComponentIdSalt;

    public function beforeAction()
    {
        $this->start();
    }

    public function afterAction()
    {
        $this->stop();
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        global $APPLICATION;

        if ($this->component->arParams['USE_AJAX'] !== 'Y') {
            return false;
        }

        if (strlen($this->component->arParams['AJAX_PARAM_NAME']) <= 0) {
            $this->component->arParams['AJAX_PARAM_NAME'] = 'compid';
        }

        if (strlen($this->component->arParams['AJAX_COMPONENT_ID']) <= 0) {
            $this->component->arParams['AJAX_COMPONENT_ID'] = \CAjax::GetComponentID(
                $this->component->getName(),
                $this->component->getTemplateName(),
                $this->ajaxComponentIdSalt
            );
        }

        if ($this->isAjax()) {
            if ($this->component->arParams['AJAX_HEAD_RELOAD'] === 'Y') {
                $APPLICATION->ShowAjaxHead();
            } else {
                $APPLICATION->RestartBuffer();
            }

            if ($this->component->arParams['AJAX_TYPE'] === 'JSON') {
                header('Content-Type: application/json');
            }

            if (strlen($this->component->arParams['AJAX_TEMPLATE_PAGE']) > 0) {
                $this->templatePage = basename($this->component->arParams['AJAX_TEMPLATE_PAGE']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        if ($this->isAjax() && $this->component->arParams['USE_AJAX'] === 'Y') {
            exit;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAjax()
    {
        if (
            strlen($this->component->arParams['AJAX_COMPONENT_ID']) > 0
            && strlen($this->component->arParams['AJAX_PARAM_NAME']) > 0
            && $_REQUEST[$this->component->arParams['AJAX_PARAM_NAME']] === $this->component->arParams['AJAX_COMPONENT_ID']
            && isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])
        ) {
            return true;
        }

        return false;
    }

    public function action()
    {
        if (strlen($this->component->arParams['AJAX_PARAM_NAME']) > 0
            && strlen($this->component->arParams['AJAX_COMPONENT_ID']) > 0
        ) {
            $this->component->arResult['AJAX_REQUEST_PARAMS'] =
                $this->component->arParams['AJAX_PARAM_NAME'] . '=' . $this->component->arParams['AJAX_COMPONENT_ID'];

            $this->component->setResultCacheKeys(['AJAX_REQUEST_PARAMS']);
        }
    }
}