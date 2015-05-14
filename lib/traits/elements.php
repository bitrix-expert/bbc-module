<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Traits;

use Bex\Bbc\Elements\ParamsElements;
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
     * @var bool Show include areas
     */
    public $showEditButtons = true;

    /**
     * @var \Bex\Bbc\Elements\ParamsElements
     */
    protected $paramsElements;

    protected function executePrologElements()
    {
        $this->paramsElements = new ParamsElements($this);

        $this->paramsElements->setNavStart();
        $this->paramsElements->setFilters();

        if ($this->arParams['OG_TAGS_IMAGE'])
        {
            $this->addParamsSelected([$this->arParams['OG_TAGS_IMAGE']]);
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
                $this->arParams['PAGER_SHOW_ALWAYS']
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

        if ($this->arParams['ADD_SECTIONS_CHAIN'] && is_array($this->arResult['SECTION']))
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