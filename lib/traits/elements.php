<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Traits;

/**
 * Class Elements
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
trait Elements
{
    /**
     * Generate navigation string
     *
     * @param object $result \CIBlockResult
     */
    public function generateNav($result)
    {
        /**
         * @global object $navComponentObject
         */

        if ($this->arParams['DISPLAY_BOTTOM_PAGER'] === 'Y' || $this->arParams['DISPLAY_TOP_PAGER'] === 'Y')
        {
            $this->arResult['NAV_STRING'] = $result->GetPageNavStringEx(
                $navComponentObject,
                $this->arParams['PAGER_TITLE'],
                $this->arParams['PAGER_TEMPLATE'],
                $this->arParams['PAGER_SHOW_ALWAYS']
            );
            $this->arResult['NAV_CACHED_DATA'] = $navComponentObject->GetTemplateCachedData();
            $this->arResult['NAV_RESULT'] = $result;
        }
    }

    /**
     * Processing request of the elements
     *
     * @param \CIBlockResult $element
     * @return array
     */
    protected function processingElementsResult($element)
    {
        $arElement = $element;

        if ($this->arParams['RESULT_PROCESSING_MODE'] === 'EXTENDED')
        {
            $arElement = $element->GetFields();
            $arElement['PROPS'] = $element->GetProperties();
        }
        elseif (!empty($this->arParams['SELECT_PROPS']))
        {
            foreach ($this->arParams['SELECT_PROPS'] as $propCode)
            {
                if (trim($propCode))
                {
                    $arProp = explode('.', $propCode);
                    $propCode = array_shift($arProp);
                    $propValue = $element['PROPERTY_'.$propCode.'_VALUE'];
                    $propDescr = $element['PROPERTY_'.$propCode.'_DESCRIPTION'];

                    if ($propValue)
                    {
                        $arElement['PROPS'][$propCode]['VALUE'] = $propValue;
                    }

                    if ($propDescr)
                    {
                        $arElement['PROPS'][$propCode]['DESCRIPTION'] = $propDescr;
                    }

                    if (!empty($arElement['PROPS'][$propCode]))
                    {
                        foreach ($arProp as $field)
                        {
                            $arElement['PROPS'][$propCode]['LINKED'][$field] = $element['PROPERTY_'.$propCode.'_'.$field];
                        }
                    }
                }
            }
        }

        if ($arElement = $this->prepareElementsResult($arElement))
        {
            return $arElement;
        }
        else
        {
            return false;
        }
    }

    /**
     * Method for prepare result request of the elements
     *
     * @param \CIBlockResult $element Result element fields
     * @return bool
     */
    public function prepareElementsResult($element)
    {
        return $element;
    }
}