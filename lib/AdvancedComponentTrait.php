<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\AdvancedComponent;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
trait AdvancedComponentTrait
{
    /**
     * @var PluginManager
     */
    public $pluginManager;

    public function configurate()
    {
    }

    /**
     * @see \CBitrixComponent::executeComponent()
     */
    public function executeComponent()
    {
        try {
            $this->executeAdvancedComponent();
        } catch (\Exception $e) {
            ShowError($e->getMessage());
        }
    }

    /**
     * Calls in the $this->executeComponent()
     */
    protected function executeAdvancedComponent()
    {
        $this->pluginManager = new PluginManager($this);

        $this->pluginManager->trigger('executeInit');

        $this->pluginManager->trigger('executeProlog');
        $this->executeProlog();

        $this->executeMain();
        $this->pluginManager->trigger('executeMain');

//        $this->render();

        $this->pluginManager->trigger('executeEpilog');
        $this->executeEpilog();

        $this->pluginManager->trigger('executeFinal');
    }

    /**
     * Execute before getting results. Not cached
     */
    public function executeProlog()
    {

    }

    /**
     * A method for extending the results of the child classes
     */
    public function executeMain()
    {
        parent::executeComponent();
    }

    /**
     * Execute after getting results. Not cached
     */
    public function executeEpilog()
    {

    }
}