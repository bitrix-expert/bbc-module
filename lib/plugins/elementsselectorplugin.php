<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 *
 * @todo Add .parameters.php
 * @todo Rename plugin
 */
class ElementsSelectorPlugin extends Plugin
{
    /**
     * @var array|bool Group parameters for \CIBlockElement::GetList()
     */
    private $groupingParams;
    /**
     * @var array Values of global filter
     */
    private $filterParams = [];
    /**
     * @var array|bool Paginator parameters for \CIBlockElement::GetList()
     */
    private $navStartParams;
    /**
     * @var string Method name for prepare result request of the elements
     */
    public $processingFetchCallable = 'prepareElementsResult';
    /**
     * @var CacheInterface Plugin for working with cache
     */
    protected $cache = null;

    public function configurate()
    {
        $this->cache = $this->getPlugin(PluginInterface::CACHE);
    }

    public function beforeAction()
    {
        $this->setNavStart();
        $this->setFilters();
    }

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

        if ($this->component->arParams['DISPLAY_BOTTOM_PAGER'] === 'Y'
            || $this->component->arParams['DISPLAY_TOP_PAGER'] === 'Y')
        {
            $this->component->arResult['NAV_STRING'] = $result->GetPageNavStringEx(
                $navComponentObject,
                $this->component->arParams['PAGER_TITLE'],
                $this->component->arParams['PAGER_TEMPLATE'],
                $this->component->arParams['PAGER_SHOW_ALWAYS']
            );
            $this->component->arResult['NAV_CACHED_DATA'] = $navComponentObject->GetTemplateCachedData();
            $this->component->arResult['NAV_RESULT'] = $result;
        }
    }

    /**
     * Processing request of the elements
     *
     * @param \CIBlockResult $element
     * @return array
     */
    public function processingFetch($element)
    {
        $elementResult = $element;

        if ($this->component->arParams['RESULT_PROCESSING_MODE'] === 'EXTENDED')
        {
            $elementResult = $element->GetFields();
            $elementResult['PROPS'] = $element->GetProperties();
        }
        elseif (!empty($this->component->arParams['SELECT_PROPS']))
        {
            foreach ($this->component->arParams['SELECT_PROPS'] as $propCode)
            {
                if (trim($propCode))
                {
                    $arProp = explode('.', $propCode);
                    $propCode = array_shift($arProp);
                    $propValue = $element['PROPERTY_'.$propCode.'_VALUE'];
                    $propDescr = $element['PROPERTY_'.$propCode.'_DESCRIPTION'];

                    if ($propValue)
                    {
                        $elementResult['PROPS'][$propCode]['VALUE'] = $propValue;
                    }

                    if ($propDescr)
                    {
                        $elementResult['PROPS'][$propCode]['DESCRIPTION'] = $propDescr;
                    }

                    if (!empty($elementResult['PROPS'][$propCode]))
                    {
                        foreach ($arProp as $field)
                        {
                            $elementResult['PROPS'][$propCode]['LINKED'][$field] = $element['PROPERTY_'.$propCode.'_'.$field];
                        }
                    }
                }
            }
        }

        $callable = $this->processingFetchCallable;

        if (method_exists($this->component, $callable))
        {
            if ($elementResult = $this->component->$callable($elementResult))
            {
                return $elementResult;
            }
        }

        return $elementResult;
    }

    public function setNavStart()
    {
        if ($this->component->arParams['PAGER_SAVE_SESSION'] !== 'Y')
        {
            \CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
        }

        $this->component->arParams['PAGER_DESC_NUMBERING'] = $this->component->arParams['PAGER_DESC_NUMBERING'] === 'Y';

        if ($this->component->arParams['DISPLAY_BOTTOM_PAGER'] === 'Y' || $this->component->arParams['DISPLAY_TOP_PAGER'] === 'Y')
        {
            $this->navStartParams = [
                'nPageSize' => $this->component->arParams['ELEMENTS_COUNT'],
                'bDescPageNumbering' => $this->component->arParams['PAGER_DESC_NUMBERING'],
                'bShowAll' => $this->component->arParams['PAGER_SHOW_ALL']
            ];

            if ($this->cache)
            {
                $this->cache->addId(\CDBResult::GetNavParams($this->navStartParams));
            }
        }
        elseif ($this->component->arParams['ELEMENTS_COUNT'] > 0)
        {
            $this->navStartParams = [
                'nTopCount' => $this->component->arParams['ELEMENTS_COUNT'],
                'bDescPageNumbering' => $this->component->arParams['PAGER_DESC_NUMBERING']
            ];
        }
        else
        {
            $this->navStartParams = false;
        }
    }

    /**
     * Setting filters. Reading of the global filter and writing his to component parameters
     */
    public function setFilters()
    {
        $globalFilter = $GLOBALS[$this->component->arParams['EX_FILTER_NAME']];

        if ($this->component->arParams['IBLOCK_TYPE'])
        {
            $this->filterParams['IBLOCK_TYPE'] = $this->component->arParams['IBLOCK_TYPE'];
        }

        if ($this->component->arParams['IBLOCK_ID'])
        {
            $this->filterParams['IBLOCK_ID'] = $this->component->arParams['IBLOCK_ID'];
        }

        if ($this->component->arParams['SECTION_CODE'])
        {
            $this->filterParams['SECTION_CODE'] = $this->component->arParams['SECTION_CODE'];
        }
        elseif ($this->component->arParams['SECTION_ID'])
        {
            $this->filterParams['SECTION_ID'] = $this->component->arParams['SECTION_ID'];
        }

        if ($this->component->arParams['INCLUDE_SUBSECTIONS'] === 'Y')
        {
            $this->filterParams['INCLUDE_SUBSECTIONS'] = 'Y';
        }

        if ($this->component->arParams['ELEMENT_CODE'])
        {
            $this->filterParams['CODE'] = $this->component->arParams['ELEMENT_CODE'];
        }
        elseif ($this->component->arParams['ELEMENT_ID'])
        {
            $this->filterParams['ID'] = $this->component->arParams['ELEMENT_ID'];
        }

        if ($this->component->arParams['CHECK_PERMISSIONS'])
        {
            $this->filterParams['CHECK_PERMISSIONS'] = $this->component->arParams['CHECK_PERMISSIONS'];
        }

        if (!isset($this->filterParams['ACTIVE']))
        {
            $this->filterParams['ACTIVE'] = 'Y';
        }

        if (strlen($this->component->arParams['EX_FILTER_NAME']) > 0
            && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $this->component->arParams['EX_FILTER_NAME'])
            && is_array($globalFilter)
        )
        {
            $this->filterParams = array_merge_recursive($this->filterParams, $globalFilter);

            if ($this->cache)
            {
                $this->cache->addId($globalFilter);
            }
        }
    }

    /**
     * Add new fields to global filter
     *
     * @param array $fields
     */
    public function addGlobalFilters(array $fields)
    {
        if (is_array($fields) && !empty($fields))
        {
            $this->filterParams = array_merge_recursive($this->filterParams, $fields);

            if ($this->cache)
            {
                $this->cache->addId($fields);
            }
        }
    }

    /**
     * Add parameters to grouping
     *
     * @param array $fields
     * @uses groupingParams
     */
    public function addGrouping($fields = [])
    {
        if (is_array($fields) && !empty($fields))
        {
            $this->groupingParams = array_merge($this->groupingParams, $fields);
        }
    }

    /**
     * Add parameters to pagination settings
     *
     * @param array $params
     * @uses navStartParams
     */
    public function addNavStart($params = [])
    {
        if (is_array($params) && !empty($params))
        {
            $this->navStartParams = array_merge($this->navStartParams, $params);
        }
    }

    /**
     * Add selected fields and properties to parameters
     *
     * @param array $fields
     * @param array $props
     */
    public function addSelected($fields = null, $props = null)
    {
        if (is_array($fields) && !empty($fields))
        {
            $this->component->arParams['SELECT_FIELDS'] = array_merge($this->component->arParams['SELECT_FIELDS'], $fields);
        }

        if (is_array($props) && !empty($props))
        {
            $this->component->arParams['SELECT_PROPS'] = array_merge($this->component->arParams['SELECT_PROPS'], $props);
        }
    }

    /**
     * Returns prepare parameters of sort of the component
     *
     * @param array $additionalFields Additional fields for sorting
     * @return array
     */
    public function getSort($additionalFields = [])
    {
        $this->component->arParams['SORT_BY_1'] = trim($this->component->arParams['SORT_BY_1']);

        if (strlen($this->component->arParams['SORT_BY_1']) <= 0)
        {
            $this->component->arParams['SORT_BY_1'] = 'ACTIVE_FROM';
        }

        if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $this->component->arParams['SORT_ORDER_1']))
        {
            $this->component->arParams['SORT_ORDER_1'] = 'DESC';
        }

        if (strlen($this->component->arParams['SORT_BY_2']) <= 0)
        {
            $this->component->arParams['SORT_BY_2'] = 'SORT';
        }

        if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $this->component->arParams['SORT_ORDER_2']))
        {
            $this->component->arParams['SORT_ORDER_2'] = 'ASC';
        }

        $fields = [
            $this->component->arParams['SORT_BY_1'] => $this->component->arParams['SORT_ORDER_1'],
            $this->component->arParams['SORT_BY_2'] => $this->component->arParams['SORT_ORDER_2']
        ];

        if (is_array($additionalFields) && !empty($additionalFields))
        {
            $fields = array_merge($fields, $additionalFields);
        }

        return $fields;
    }

    /**
     * Returns array filters fields for uses in \CIBlock...::GetList().
     *
     * Returns array with values global filter and (if is set in $this->component->arParams)
     * <ul>
     * <li> IBLOCK_TYPE
     * <li> IBLOCK_ID
     * <li> SECTION_ID
     * </ul>
     *
     * @param array $additionalFields
     * @return array
     */
    public function getFilters($additionalFields = [])
    {
        if (is_array($additionalFields) && !empty($additionalFields))
        {
            $this->filterParams = array_merge_recursive($this->filterParams, $additionalFields);
        }

        return $this->filterParams;
    }

    /**
     * Returns array with pagination parameters for uses in \CIBlock...::GetList()
     *
     * @param array $additionalFields
     * @uses navStartParams
     * @return array|bool
     */
    public function getNavStart($additionalFields = [])
    {
        if (!empty($additionalFields))
        {
            $this->addNavStart($additionalFields);
        }

        return $this->navStartParams;
    }

    /**
     * Returns array with group parameters for uses in \CIBlock...::GetList()
     *
     * @param array $additionalFields
     * @uses groupingParams
     * @return array|bool
     */
    public function getGrouping($additionalFields = [])
    {
        if (!empty($additionalFields))
        {
            $this->addGrouping($additionalFields);
        }

        return $this->groupingParams;
    }

    /**
     * Returns array with selected fields and properties for uses in \CIBlock...::GetList()
     *
     * @param array $additionalFields Additional fields
     * @param string $propsPrefix Prefix for properties keys
     * @return array
     */
    public function getSelected($additionalFields = [], $propsPrefix = 'PROPERTY_')
    {
        $fields = [
            'ID',
            'IBLOCK_ID',
            'IBLOCK_SECTION_ID',
            'NAME'
        ];

        if (!empty($this->component->arParams['SELECT_FIELDS']))
        {
            foreach ($this->component->arParams['SELECT_FIELDS'] as $field)
            {
                if (trim($field))
                {
                    $fields[] = $field;
                }
            }

            unset($field);
        }

        if (!empty($this->component->arParams['SELECT_PROPS']))
        {
            foreach ($this->component->arParams['SELECT_PROPS'] as $propCode)
            {
                if (trim($propCode))
                {
                    $fields[] = $propsPrefix.$propCode;
                }
            }
        }

        if (is_array($additionalFields) && !empty($additionalFields))
        {
            $fields = array_merge($fields, $additionalFields);
        }

        return array_unique($fields);
    }

    public function getProcessingMethod()
    {
        if ($this->component->arParams['RESULT_PROCESSING_MODE'] === 'EXTENDED')
        {
            return 'GetNextElement';
        }
        else
        {
            return 'GetNext';
        }
    }
}