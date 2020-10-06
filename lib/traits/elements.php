<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Traits;

use Bitrix\Iblock\InheritedProperty;
use Bitrix\Main\Page\Asset;

/**
 * Class Elements
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
trait Elements
{
    /**
     * @var array|bool Paginator parameters for \CIBlockElement::GetList()
     */
    private $navStartParams;

    /**
     * @var array|bool Group parameters for \CIBlockElement::GetList()
     */
    private $groupingParams;

    /**
     * @var array Values of global filter
     */
    private $filterParams = [];

    /**
     * @var bool Show include areas
     */
    public $showEditButtons = true;

    protected function executePrologElements()
    {
        $this->setNavStartParams();
        $this->setParamsFilters();

        if ($this->arParams['OG_TAGS_IMAGE'])
        {
            $this->addParamsSelected([$this->arParams['OG_TAGS_IMAGE']]);
        }
    }

    protected function setNavStartParams()
    {
        if ($this->arParams['PAGER_SAVE_SESSION'] !== 'Y')
        {
            \CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
        }

        $this->arParams['PAGER_DESC_NUMBERING'] = $this->arParams['PAGER_DESC_NUMBERING'] === 'Y';

        if ($this->arParams['DISPLAY_BOTTOM_PAGER'] === 'Y' || $this->arParams['DISPLAY_TOP_PAGER'] === 'Y')
        {
            $this->navStartParams = [
                'nPageSize' => $this->arParams['ELEMENTS_COUNT'],
                'bDescPageNumbering' => $this->arParams['PAGER_DESC_NUMBERING'],
                'bShowAll' => $this->arParams['PAGER_SHOW_ALL']
            ];

            $this->addCacheAdditionalId(\CDBResult::GetNavParams($this->navStartParams));
        }
        elseif ($this->arParams['ELEMENTS_COUNT'] > 0)
        {
            $this->navStartParams = [
                'nTopCount' => $this->arParams['ELEMENTS_COUNT'],
                'bDescPageNumbering' => $this->arParams['PAGER_DESC_NUMBERING']
            ];
        }
        else
        {
            $this->navStartParams = false;
        }
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

        if ($this->arParams['DISPLAY_BOTTOM_PAGER'] === 'Y' || $this->arParams['DISPLAY_TOP_PAGER'] === 'Y')
        {
            $this->arResult['NAV_STRING'] = $result->GetPageNavStringEx(
                $navComponentObject,
                $this->arParams['PAGER_TITLE'],
                $this->arParams['PAGER_TEMPLATE'],
                ($this->arParams['PAGER_SHOW_ALWAYS'] === 'Y' ? true : false)
            );
            $this->arResult['NAV_CACHED_DATA'] = $navComponentObject->GetTemplateCachedData();
            $this->arResult['NAV_RESULT'] = $result;
        }
    }

    protected function executeMainElements()
    {
        // todo Move to getFilterParams()
        if ($this->arParams['SECTION_CODE'] && !$this->arParams['SECTION_ID'])
        {
            $this->arParams['SECTION_ID'] = \CIBlockFindTools::GetSectionID(
                0,
                $this->arParams['SECTION_CODE'],
                []
            );
        }

        if ($this->arParams['ELEMENT_CODE'] && !$this->arParams['ELEMENT_ID'])
        {
            $this->arParams['ELEMENT_ID'] = \CIBlockFindTools::GetElementID(
                0,
                $this->arParams['ELEMENT_CODE'],
                $this->arParams['SECTION_ID'],
                $this->arParams['SECTION_CODE'],
                []
            );
        }

        $this->readInheritedProps();
        $this->readSectionParams();
        $this->readOgDatas();
    }

    protected function readInheritedProps()
    {
        if ($this->arParams['SET_SEO_TAGS'] !== 'Y' || !$this->arParams['IBLOCK_ID'])
        {
            if ($this->arParams['OG_TAGS_TITLE'] !== 'SEO_TITLE' && $this->arParams['OG_TAGS_DESCRIPTION'] !== 'SEO_DESCRIPTION')
            {
                return false;
            }
        }

        if ($this->arParams['ELEMENT_ID'])
        {
            $rsSeoValues = new InheritedProperty\ElementValues($this->arParams['IBLOCK_ID'], $this->arParams['ELEMENT_ID']);
            $seoValues = $rsSeoValues->getValues();

            if (!$this->arResult['SEO_TAGS']['TITLE'])
            {
                $this->arResult['SEO_TAGS']['TITLE'] = $seoValues['ELEMENT_META_TITLE'];
            }

            if (!$this->arResult['SEO_TAGS']['DESCRIPTION'])
            {
                $this->arResult['SEO_TAGS']['DESCRIPTION'] = $seoValues['ELEMENT_META_DESCRIPTION'];
            }

            if (!$this->arResult['SEO_TAGS']['KEYWORDS'])
            {
                $this->arResult['SEO_TAGS']['KEYWORDS'] = $seoValues['ELEMENT_META_KEYWORDS'];
            }
        }
        elseif ($this->arParams['SECTION_ID'])
        {
            $rsSeoValues = new InheritedProperty\SectionValues($this->arParams['IBLOCK_ID'], $this->arParams['SECTION_ID']);
            $seoValues = $rsSeoValues->getValues();

            if (!$this->arResult['SEO_TAGS']['TITLE'])
            {
                $this->arResult['SEO_TAGS']['TITLE'] = $seoValues['SECTION_META_TITLE'];
            }

            if (!$this->arResult['SEO_TAGS']['DESCRIPTION'])
            {
                $this->arResult['SEO_TAGS']['DESCRIPTION'] = $seoValues['SECTION_META_DESCRIPTION'];
            }

            if (!$this->arResult['SEO_TAGS']['KEYWORDS'])
            {
                $this->arResult['SEO_TAGS']['KEYWORDS'] = $seoValues['SECTION_META_KEYWORDS'];
            }
        }

        if (!empty($this->arResult['SEO_TAGS']) && is_array($this->arResult['SEO_TAGS']))
        {
            foreach ($this->arResult['SEO_TAGS'] as &$field)
            {
                $field = strip_tags($field);
            }

            unset ($field);
        }

        if (!empty($this->arResult['SEO_TAGS']))
        {
            $this->setResultCacheKeys(['SEO_TAGS']);
        }
    }

    protected function readSectionParams()
    {
        if ($this->arResult['IBLOCK_SECTION_ID'])
        {
            $this->arParams['SECTION_ID'] = $this->arResult['IBLOCK_SECTION_ID'];
        }

        if ($this->arParams['SECTION_ID'] > 0)
        {
            $this->arResult['SECTION'] = ['PATH' => []];

            $rsPath = \CIBlockSection::GetNavChain($this->arParams['IBLOCK_ID'], $this->arParams['SECTION_ID']);
            $rsPath->SetUrlTemplates('', $this->arParams['SECTION_URL'], $this->arParams['IBLOCK_URL']);

            while ($arPath = $rsPath->GetNext())
            {
                $ipropValues = new InheritedProperty\SectionValues($this->arParams['IBLOCK_ID'], $arPath['ID']);
                $arPath['IPROPERTY_VALUES'] = $ipropValues->getValues();
                $this->arResult['SECTION']['PATH'][] = $arPath;
            }

            $ipropValues = new InheritedProperty\SectionValues($this->arParams['IBLOCK_ID'], $this->arParams['SECTION_ID']);
            $this->arResult['IPROPERTY_VALUES'] = $ipropValues->getValues();
        }
        else
        {
            $this->arResult['SECTION'] = false;
        }

        $this->setResultCacheKeys(['SECTION']);
    }

    protected function readOgDatas()
    {
        global $APPLICATION;

        if (!$this->arResult['OG_TAGS']['TITLE'])
        {
            if ($this->arParams['OG_TAGS_TITLE'] === 'SEO_TITLE')
            {
                $this->arResult['OG_TAGS']['TITLE'] = strip_tags($this->arResult['SEO_TAGS']['TITLE']);
            }
            elseif ($this->arParams['OG_TAGS_TITLE'])
            {
                $this->arResult['OG_TAGS']['TITLE'] = strip_tags($this->arResult[$this->arParams['OG_TAGS_TITLE']]);
            }
        }

        if (!$this->arResult['OG_TAGS']['DESCRIPTION'])
        {
            if ($this->arParams['OG_TAGS_DESCRIPTION'] === 'SEO_DESCRIPTION')
            {
                $this->arResult['OG_TAGS']['DESCRIPTION'] = strip_tags($this->arResult['SEO_TAGS']['DESCRIPTION']);
            }
            elseif ($this->arParams['OG_TAGS_DESCRIPTION'])
            {
                $this->arResult['OG_TAGS']['DESCRIPTION'] = strip_tags($this->arResult[$this->arParams['OG_TAGS_DESCRIPTION']]);
            }
        }

        if (!$this->arResult['OG_TAGS']['IMAGE'] && $this->arParams['OG_TAGS_IMAGE'])
        {
            $file = \CFile::GetPath($this->arResult[$this->arParams['OG_TAGS_IMAGE']]);

            if ($file)
            {
                $this->arResult['OG_TAGS']['IMAGE'] = 'http://'.SITE_SERVER_NAME.$file;
            }
        }

        if ($this->arParams['OG_TAGS_URL'] === 'SHORT_LINK')
        {
            $this->arResult['OG_TAGS']['URL'] = $this->getShortLink($APPLICATION->GetCurPage());
        }

        if (!empty($this->arResult['OG_TAGS']) && is_array($this->arResult['OG_TAGS']))
        {
            foreach ($this->arResult['OG_TAGS'] as &$field)
            {
                $field = strip_tags($field);
            }

            unset ($field);
        }

        if (!empty($this->arResult['OG_TAGS']))
        {
            $this->setResultCacheKeys(['OG_TAGS']);
        }
    }

    /**
     * Returns short link
     *
     * @param string $fullLink
     * @return string
     */
    public static function getShortLink($fullLink)
    {
        $prefix = 'http://'.SITE_SERVER_NAME.'/';

        $rsShortLink = \CBXShortUri::GetList(
            [],
            [
                'URI' => $fullLink
            ]
        );

        if ($shortLink = $rsShortLink->Fetch())
        {
            return $prefix.$shortLink['SHORT_URI'];
        }

        $shortLink = \CBXShortUri::GenerateShortUri();

        $id = \CBXShortUri::Add(
            [
                'URI' => $fullLink,
                'SHORT_URI' => $shortLink,
                'STATUS' => '301',
            ]
        );

        if ($id)
        {
            return $prefix.$shortLink;
        }
    }

    /**
     * Setting meta tags
     *
     * <ul> Uses:
     * <li> title
     * <li> description
     * <li> keywords
     * </ul>
     *
     * @uses arResult['SEO_TAGS']
     */
    protected function setSeoTags()
    {
        global $APPLICATION;

        if ($this->arParams['SET_SEO_TAGS'] !== 'Y')
        {
            return false;
        }

        if ($this->arResult['SEO_TAGS']['TITLE'])
        {
            $APPLICATION->SetPageProperty('title', $this->arResult['SEO_TAGS']['TITLE']);
        }

        if ($this->arResult['SEO_TAGS']['DESCRIPTION'])
        {
            $APPLICATION->SetPageProperty('description', $this->arResult['SEO_TAGS']['DESCRIPTION']);
        }

        if ($this->arResult['SEO_TAGS']['KEYWORDS'])
        {
            $APPLICATION->SetPageProperty('keywords', $this->arResult['SEO_TAGS']['KEYWORDS']);
        }
    }

    protected function setNavChain()
    {
        global $APPLICATION;

        if ($this->arParams['ADD_SECTIONS_CHAIN'] === 'Y' && is_array($this->arResult['SECTION']))
        {
            foreach ($this->arResult['SECTION']['PATH'] as $path)
            {
                if ($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'])
                {
                    $APPLICATION->AddChainItem($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $path['~SECTION_PAGE_URL']);
                }
                else
                {
                    $APPLICATION->AddChainItem($path['NAME'], $path['~SECTION_PAGE_URL']);
                }
            }
        }

        if ($this->arParams['ADD_ELEMENT_CHAIN'] === 'Y' && $this->arResult['NAME'])
        {
            $APPLICATION->AddChainItem($this->arResult['NAME']);
        }
    }

    /**
     * Setting open graph tags for current page
     *
     * <ul> Uses:
     * <li> og:title
     * <li> og:url
     * <li> og:image
     * </ul>
     *
     * @uses arResult['OG_TAGS']
     */
    protected function setOgTags()
    {
        if ($this->arResult['OG_TAGS']['TITLE'])
        {
            Asset::getInstance()->addString('<meta property="og:title" content="'.$this->arResult['OG_TAGS']['TITLE'].'" />', true);
        }

        if ($this->arResult['OG_TAGS']['DESCRIPTION'])
        {
            Asset::getInstance()->addString('<meta property="og:description" content="'.$this->arResult['OG_TAGS']['DESCRIPTION'].'" />', true);
        }

        if ($this->arResult['OG_TAGS']['URL'])
        {
            Asset::getInstance()->addString('<meta property="og:url" content="'.$this->arResult['OG_TAGS']['URL'].'" />', true);
        }

        if ($this->arResult['OG_TAGS']['IMAGE'])
        {
            Asset::getInstance()->addString('<meta property="og:image" content="'.$this->arResult['OG_TAGS']['IMAGE'].'" />', true);
        }
    }

    /**
     * Add to page buttons for edit elements and sections of info-block
     */
    protected function setEditButtons()
    {
        global $APPLICATION;

        if (!$APPLICATION->GetShowIncludeAreas() || $this->showEditButtons === false)
        {
            return false;
        }

        $buttons = \CIBlock::GetPanelButtons(
            $this->arParams['IBLOCK_ID'],
            $this->arResult['ID'],
            $this->arParams['SECTION_ID'],
            []
        );

        $this->addIncludeAreaIcons(\CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));

        if (is_array($buttons['intranet']))
        {
            Asset::getInstance()->addJs(BX_ROOT.'/js/main/utils.js');

            foreach ($buttons['intranet'] as $button)
            {
                $this->addEditButton($button);
            }
        }
    }

    /**
     * Getting global filter and write his to component parameters
     */
    protected function setParamsFilters()
    {
        if ($this->arParams['IBLOCK_TYPE'])
        {
            $this->filterParams['IBLOCK_TYPE'] = $this->arParams['IBLOCK_TYPE'];
        }

        if ($this->arParams['IBLOCK_ID'])
        {
            $this->filterParams['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
        }

        if ($this->arParams['SECTION_CODE'])
        {
            $this->filterParams['SECTION_CODE'] = $this->arParams['SECTION_CODE'];
        }
        elseif ($this->arParams['SECTION_ID'])
        {
            $this->filterParams['SECTION_ID'] = $this->arParams['SECTION_ID'];
        }

        if ($this->arParams['INCLUDE_SUBSECTIONS'] === 'Y')
        {
            $this->filterParams['INCLUDE_SUBSECTIONS'] = 'Y';
        }

        if ($this->arParams['ELEMENT_CODE'])
        {
            $this->filterParams['CODE'] = $this->arParams['ELEMENT_CODE'];
        }
        elseif ($this->arParams['ELEMENT_ID'])
        {
            $this->filterParams['ID'] = $this->arParams['ELEMENT_ID'];
        }

        if ($this->arParams['CHECK_PERMISSIONS'])
        {
            $this->filterParams['CHECK_PERMISSIONS'] = $this->arParams['CHECK_PERMISSIONS'];
        }

        if (!isset($this->filterParams['ACTIVE']))
        {
            $this->filterParams['ACTIVE'] = 'Y';
        }

        if (strlen($this->arParams['EX_FILTER_NAME']) > 0
            && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $this->arParams['EX_FILTER_NAME'])
            && is_array($GLOBALS[$this->arParams['EX_FILTER_NAME']])
        )
        {
            $this->filterParams = array_merge_recursive($this->filterParams, $GLOBALS[$this->arParams['EX_FILTER_NAME']]);

            $this->addCacheAdditionalId($GLOBALS[$this->arParams['EX_FILTER_NAME']]);
        }
    }

    /**
     * Add new fields to global filter
     *
     * @param array $fields Array with fields
     * @param bool $recursiveMerge If true, $fields will be recursive merged with
     * other parameters (used array_merge_recursive()), otherwise not recursive merge (used array_merge()).
     */
    public function addGlobalFilters(array $fields, $recursiveMerge = false)
    {
        if (is_array($fields) && !empty($fields))
        {
            if ($recursiveMerge)
            {
                $this->filterParams = array_merge_recursive($this->filterParams, $fields);
            }
            else
            {
                $this->filterParams = array_merge($this->filterParams, $fields);
            }

            $this->addCacheAdditionalId($fields);
        }
    }

    /**
     * Add parameters to grouping
     *
     * @param array $fields
     * @uses groupingParams
     */
    public function addParamsGrouping($fields = [])
    {
        if (is_array($fields) && !empty($fields))
        {
            $this->groupingParams = array_merge(is_array($this->groupingParams) ? $this->groupingParams : [], $fields);
        }
    }

    /**
     * Add parameters to pagination settings
     *
     * @param array $params
     * @uses navStartParams
     */
    public function addParamsNavStart($params = [])
    {
        if (is_array($params) && !empty($params))
        {
            $this->navStartParams = array_merge(is_array($this->navStartParams) ? $this->navStartParams : array(), $params);
        }
    }

    /**
     * Add selected fields and properties to parameters
     *
     * @param array $fields
     * @param array $props
     */
    public function addParamsSelected($fields = null, $props = null)
    {
        if (is_array($fields) && !empty($fields))
        {
            $this->arParams['SELECT_FIELDS'] = array_merge($this->arParams['SELECT_FIELDS'], $fields);
        }

        if (is_array($props) && !empty($props))
        {
            $this->arParams['SELECT_PROPS'] = array_merge($this->arParams['SELECT_PROPS'], $props);
        }
    }

    /**
     * Returns prepare parameters of sort of the component
     *
     * @param array $additionalFields Additional fields for sorting
     * @return array
     */
    public function getParamsSort($additionalFields = [])
    {
        $this->arParams['SORT_BY_1'] = trim($this->arParams['SORT_BY_1']);

        if (strlen($this->arParams['SORT_BY_1']) <= 0)
        {
            $this->arParams['SORT_BY_1'] = 'ACTIVE_FROM';
        }

        if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $this->arParams['SORT_ORDER_1']))
        {
            $this->arParams['SORT_ORDER_1'] = 'DESC';
        }

        if (strlen($this->arParams['SORT_BY_2']) <= 0)
        {
            $this->arParams['SORT_BY_2'] = 'SORT';
        }

        if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $this->arParams['SORT_ORDER_2']))
        {
            $this->arParams['SORT_ORDER_2'] = 'ASC';
        }

        $fields = [
            $this->arParams['SORT_BY_1'] => $this->arParams['SORT_ORDER_1'],
            $this->arParams['SORT_BY_2'] => $this->arParams['SORT_ORDER_2']
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
     * Returns array with values global filter and (if is set in $this->arParams)
     * <ul>
     * <li> IBLOCK_TYPE
     * <li> IBLOCK_ID
     * <li> SECTION_ID
     * </ul>
     *
     * @param array $additionalFields
     * @return array
     */
    public function getParamsFilters($additionalFields = [])
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
    public function getParamsNavStart($additionalFields = [])
    {
        if (!empty($additionalFields))
        {
            $this->addParamsNavStart($additionalFields);
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
    public function getParamsGrouping($additionalFields = [])
    {
        if (!empty($additionalFields))
        {
            $this->addParamsGrouping($additionalFields);
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
    public function getParamsSelected($additionalFields = [], $propsPrefix = 'PROPERTY_')
    {
        $fields = [
            'ID',
            'IBLOCK_ID',
            'IBLOCK_SECTION_ID',
            'NAME'
        ];

        if (!empty($this->arParams['SELECT_FIELDS']))
        {
            foreach ($this->arParams['SELECT_FIELDS'] as $field)
            {
                if (trim($field))
                {
                    $fields[] = $field;
                }
            }

            unset($field);
        }

        if (!empty($this->arParams['SELECT_PROPS']))
        {
            foreach ($this->arParams['SELECT_PROPS'] as $propCode)
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
        if ($this->arParams['RESULT_PROCESSING_MODE'] === 'EXTENDED')
        {
            return 'GetNextElement';
        }
        else
        {
            return 'GetNext';
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

    protected function executeEpilogElements()
    {
        $this->setSeoTags();
        $this->setOgTags();
        $this->setNavChain();
        $this->setEditButtons();
    }
}
