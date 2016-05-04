<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc;

use Bex\Bbc\Plugins\PluginDispatcher;

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
    public $defaultAction = 'index';
    /**
     * @var string Name of action
     */
    public $action;
    /**
     * @var PluginDispatcher
     */
    private $pluginDispatcher;

    public function plugins()
    {
        return [
            'cache' => '\Bex\Bbc\Plugins\CachePlugin',
            'ajax' => '\Bex\Bbc\Plugins\AjaxPlugin',
            'catcher' => '\Bex\Bbc\Plugins\CatcherPlugin',
            'includer' => '\Bex\Bbc\Plugins\IncluderPlugin',
            'paramsValidator' => '\Bex\Bbc\Plugins\ParamsValidatorPlugin'
        ];
    }

    public function configurate()
    {
        
    }

    public function getDispatcher()
    {
        return $this->pluginDispatcher;
    }
    
    public function getPlugin($name)
    {
        return $this->getDispatcher()->get($name);
    }

    /**
     * Configuring routes which can be component processed.
     *
     * Method must return array with configs describing routes.
     *
     * **Simple route configs**
     * ```php
     * return [
     *      'index' => '',
     *      'section' => '#SECTION_ID#/',
     *      'detail' => '#SECTION_ID#/#ELEMENT_ID#/'
     * ];
     * ```
     * * Key (index, section, detail) - action name.
     * * Value - SEF template for route
     *
     * **Routes with different requested methods**
     * ```php
     * return [
     *      'users' => [
     *          'template' => '',
     *          'method' => [
     *              'GET' => 'getUser',
     *              'POST' => 'addUser',
     *              'OPTION' => 'option',
     *          ]
     *      ],
     *      'user' => [
     *          'template' => '#USER_ID#',
     *          'method' => [
     *              'GET' => 'getUser',
     *              'POST|PUT' => 'updateUser',
     *              'DELETE' => 'deleteUser',
     *          ]
     *      ]
     * ];
     * ```
     * * Key (user, users) - action name.
     * * Value[template] - SEF template for route
     * * Value[method] - compliance requested methods of the class methods (request method => action name)
     *
     * @return array
     */
    public function routes()
    {
        return [];
    }

    /**
     * @return array
     */
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
        if (strlen($this->request->get('q')) > 0)
        {
            return true;
        }

        return false;
    }

    protected function initRouter()
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

                    if ($folder404 != $this->request->getRequestedPage())
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
        $this->pluginDispatcher = new PluginDispatcher($this);

        $this->configurate();
        $this->initRouter();

        $this->getDispatcher()->trigger('executeInit');

        $this->getDispatcher()->trigger('beforeAction');
        $this->beforeAction();

        $this->runAction();
        $this->getDispatcher()->trigger('action');

        $this->afterAction();
        $this->getDispatcher()->trigger('afterAction');
    }

    /**
     * Invoked before the action component
     */
    protected function beforeAction()
    {
    }

    protected function runAction()
    {
        $actionMethod = $this->action . 'Action';

        if (method_exists($this, $actionMethod))
        {
            $this->{$actionMethod}();
        }
        else
        {
            throw new \BadMethodCallException('Method for action "' . $this->action . '" is missing');
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
        $this->executeBasis();
    }
}