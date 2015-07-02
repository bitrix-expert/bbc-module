<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

\Bitrix\Main\Loader::registerAutoLoadClasses('bex.bbc', [
    'Bex\Bbc\Basis' => 'lib/basis.php',
    'Bex\Bbc\BasisRouter' => 'lib/basisrouter.php',

    'Bex\Bbc\CommonTrait' => 'lib/commontrait.php',
    'Bex\Bbc\ElementsTrait' => 'lib/elementstrait.php',

    'Bex\Bbc\Helpers\ComponentParameters' => 'lib/helpers/componentparameters.php',






    'Bex\AdvancedComponent\Plugin' => 'lib/Plugin.php',
    'Bex\AdvancedComponent\PluginManager' => 'lib/PluginManager.php',
    'Bex\AdvancedComponent\AdvancedComponentTrait' => 'lib/AdvancedComponentTrait.php',

    'Bex\Plugins\ErrorNotifierPlugin' => 'lib/plugins/errornotifier.php',
    'Bex\Plugins\CheckerPlugin' => 'lib/plugins/checker.php',

    'Bex\Plugins\Elements\ParamsPlugin' => 'lib/plugins/elements/params.php',
    'Bex\Plugins\Elements\SeoPlugin' => 'lib/plugins/elements/seo.php',
    'Bex\Plugins\Elements\HermitagePlugin' => 'lib/plugins/elements/hermitage.php',
]);