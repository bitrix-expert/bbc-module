<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Plugins;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Config\Option;
use Bex\Bbc\Plugin\Plugin;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ErrorNotifierPlugin extends Plugin
{
    /**
     * @var string File name of log with last exception
     */
    public $exceptionLog = 'exception.log';

    /**
     * @var bool Sending notifications to admin email
     */
    public $exceptionNotifier = true;

    /**
     * Called when an error occurs
     *
     * Resets the cache, show error message (two mode: for users and for admins),
     * sending notification to admin email
     *
     * @param \Exception $exception
     * @param bool $notifier Sent notify to admin email. Default — value of property $this->exceptionNotifier
     * @uses exceptionNotifier
     */
    public function catchException(\Exception $exception, $notifier = null)
    {
        global $USER;

        $this->abortCache(); // todo

        if ($USER->IsAdmin())
        {
            $this->showExceptionAdmin($exception);
        }
        else
        {
            $this->showExceptionUser($exception);
        }

        if (($notifier === true) || ($this->exceptionNotifier && $notifier !== false) && BX_EXC_NOTIFY !== false)
        {
            $this->sendNotifyException($exception);
        }
    }

    /**
     * Send error message to the admin email
     *
     * @param \Exception $exception
     */
    public function sendNotifyException($exception)
    {
        $adminEmail = Option::get('main', 'email_from');
        $logFile = Application::getDocumentRoot().$this->__path.'/'.$this->exceptionLog;

        if (!is_file($logFile) && $adminEmail)
        {
            $date = date('Y-m-d H:m:s');

            /**
             * @todo Loc::?
             */
            bxmail(
                $adminEmail,
                Loc::getMessage(
                    'BBC_COMPONENT_EXCEPTION_EMAIL_SUBJECT', ['#SITE_URL#' => SITE_SERVER_NAME]
                ),
                Loc::getMessage(
                    'BBC_COMPONENT_EXCEPTION_EMAIL_TEXT',
                    [
                        '#URL#' => 'http://'.SITE_SERVER_NAME.Context::getCurrent()->getRequest()->getRequestedPage(),
                        '#DATE#' => $date,
                        '#EXCEPTION_MESSAGE#' => $exception->getMessage(),
                        '#EXCEPTION#' => $exception
                    ]
                ),
                'Content-Type: text/html; charset=utf-8'
            );

            $log = fopen($logFile, 'w');
            fwrite($log, '['.$date.'] Catch exception: '.PHP_EOL.$exception);
            fclose($log);
        }
    }

    /**
     * Display of the error for user
     *
     * @param \Exception $exception
     */
    protected function showExceptionUser(\Exception $exception)
    {
        // todo set HTTP status and constant

        ShowError(Loc::getMessage('BBC_COMPONENT_CATCH_EXCEPTION'));
    }

    /**
     * Display of the error for admin
     *
     * @param \Exception $exception
     */
    protected function showExceptionAdmin(\Exception $exception)
    {
        ShowError($exception->getMessage());

        echo nl2br($exception);
    }

    public function executeFinal()
    {
        if ($this->exceptionNotifier)
        {
            $logFile = Application::getDocumentRoot() . $this->__path . '/' . $this->exceptionLog;

            if (is_file($logFile))
            {
                unlink($logFile);
            }
        }
    }
}