<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

\Bitrix\Main\Loader::registerAutoLoadClasses('bex.bbc', [
    'Bex\Bbc\Basis' => 'lib/basis.php',
    'Bex\Bbc\BasisRouter' => 'lib/basisrouter.php',

    'Bex\Bbc\Traits\Common' => 'lib/traits/common.php',
    'Bex\Bbc\Traits\Elements' => 'lib/traits/elements.php',

    'Bex\Bbc\Elements\ParamsElements' => 'lib/elements/paramselements.php',

    'Bex\Bbc\Helpers\ComponentParameters' => 'lib/helpers/componentparameters.php',
]);