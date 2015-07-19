<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

\Bitrix\Main\Loader::registerAutoLoadClasses('bex.bbc', [
    'Bex\Bbc\Basis' => 'lib/basiscomponent.php',
    'Bex\Bbc\BasisRouter' => 'lib/basisrouter.php',

    'Bex\Bbc\CommonTrait' => 'lib/commontrait.php',
    'Bex\Bbc\ElementsTrait' => 'lib/elementstrait.php',

    'Bex\Bbc\Helpers\ComponentParameters' => 'lib/helpers/componentparameters.php',

    'Bex\Bbc\Plugins\Plugin' => 'lib/plugins/plugin.php',
    'Bex\Bbc\Plugins\PluginManager' => 'lib/plugins/pluginmanager.php',



    'Bex\Bbc\Plugins\ErrorNotifierPlugin' => 'lib/plugins/errornotifier.php',
    'Bex\Bbc\Plugins\IncluderPlugin' => 'lib/plugins/includer.php',
    'Bex\Bbc\Plugins\ParamsValidatorPlugin' => 'lib/plugins/paramsvalidator.php',

    'Bex\Bbc\Plugins\ElementsParamsPlugin' => 'lib/plugins/elementsparams.php',
    'Bex\Bbc\Plugins\ElementsSeoPlugin' => 'lib/plugins/elementsseo.php',
    'Bex\Bbc\Plugins\HermitagePlugin' => 'lib/plugins/hermitage.php',
]);