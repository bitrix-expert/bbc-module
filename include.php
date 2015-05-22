<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

\Bitrix\Main\Loader::registerAutoLoadClasses('bex.bbc', [
    'Bex\Bbc\Basis' => 'lib/basis.php',
    'Bex\Bbc\BasisRouter' => 'lib/basisrouter.php',

    'Bex\Bbc\Plugin\Plugin' => 'lib/plugin/plugin.php',
    'Bex\Bbc\Plugin\PluginTrait' => 'lib/plugin/plugintrait.php',

    'Bex\Bbc\Traits\Common' => 'lib/traits/common.php',
    'Bex\Bbc\Traits\Elements' => 'lib/traits/elements.php',

    'Bex\Bbc\Helpers\ComponentParameters' => 'lib/helpers/componentparameters.php',




    'Bex\Plugins\ErrorNotifierPlugin' => 'lib/plugins/errornotifier.php',

    'Bex\Plugins\Elements\ParamsPlugin' => 'lib/plugins/elements/params.php',
    'Bex\Plugins\Elements\SeoPlugin' => 'lib/plugins/elements/seo.php',
    'Bex\Plugins\Elements\HermitagePlugin' => 'lib/plugins/elements/hermitage.php',
]);