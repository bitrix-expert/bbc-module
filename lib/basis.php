<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc;

use Bex\AdvancedComponent\PluginManager;
use Bex\AdvancedComponent\AdvancedComponentTrait;
use Bex\Plugins\CheckerPlugin;
use Bex\Plugins\ErrorNotifierPlugin;

/**
 * Abstraction basis component
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
abstract class Basis extends \CBitrixComponent
{
    use Traits\Common, AdvancedComponentTrait;

    /**
     * @var bool Auto executing methods of prolog / epilog in the traits
     */
    public $traitsAutoExecute = true;

    /**
     * @var array Used traits
     */
    private $usedTraits;

    /**
     * Executing methods prolog, getResult and epilog included traits
     *
     * @param string $type prolog, getResult or epilog
     */
    private function executeTraits($type)
    {
        if (empty($this->usedTraits))
        {
            return;
        }

        switch ($type)
        {
            case 'prolog':
                $type = 'Prolog';
            break;

            case 'main':
                $type = 'Main';
            break;

            default:
                $type = 'Epilog';
            break;
        }

        foreach ($this->usedTraits as $trait => $name)
        {
            $method = 'execute'.$type.$name;

            if (method_exists($trait, $method))
            {
                $this->$method();
            }
        }
    }

    /**
     * Set to $this->usedTraits included traits
     */
    private function readUsedTraits()
    {
        if ($this->traitsAutoExecute)
        {
            $reflection = new \ReflectionClass(get_called_class());

            $parentClass = $reflection;

            while (1)
            {
                foreach ($parentClass->getTraitNames() as $trait)
                {
                    $this->usedTraits[$trait] = bx_basename($trait);
                }

                if ($parentClass->name === __CLASS__)
                {
                    break;
                }

                $parentClass = $parentClass->getParentClass();
            }
        }
    }

    public function plugins()
    {
        return [
            'errorNotifier' => ErrorNotifierPlugin::getClass(),
            'checker' => CheckerPlugin::getClass(),
        ];
    }

    /*public function configurate()
    {
        $this->addPlugin([
            ErrorNotifierPlugin::getClass(),
            [
                'class' => CheckerPlugin::getClass(),
                'prop1' => '8'
            ],
        ]);

        $this->pluginManager->add();

        CheckerPlugin::getInstance()->setCheckParams([
            'IBLOCK_ID' => ['type' => 'string']
        ]);

        $this->removePlugin(ErrorNotifierPlugin::getClass());

        echo $this->getPluginsList();
    }*/

    final protected function executeAdvancedComponent()
    {
        $pm = new PluginManager($this);

        $pm->trigger('executeInit');

        $this->readUsedTraits();

        $this->startAjax();

        $this->executeTraits('prolog');
        $pm->trigger('executeProlog');
        $this->executeProlog();

        if ($this->startCache())
        {
            $this->executeMain();
            $this->executeTraits('main');
            $pm->trigger('executeMain');

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
        $pm->trigger('executeMain');
        $this->executeEpilog();

        $pm->trigger('executeFinal');
    }

    public function executeComponent()
    {
        try {
            $this->executeAdvancedComponent();
        } catch (\Exception $e) {
            ErrorNotifierPlugin::getInstance()->catchException($e);
        }
    }
}