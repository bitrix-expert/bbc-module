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





    'Bex\Plugins\ErrorNotifierPlugin' => 'lib/plugins/errornotifier.php',
    'Bex\Plugins\IncluderPlugin' => 'lib/plugins/includer.php',
    'Bex\Plugins\ParamsValidatorPlugin' => 'lib/plugins/paramsvalidator.php',

    'Bex\Plugins\ElementsParamsPlugin' => 'lib/plugins/elementsparams.php',
    'Bex\Plugins\SeoPlugin' => 'lib/plugins/seo.php',
    'Bex\Plugins\HermitagePlugin' => 'lib/plugins/hermitage.php',
]);