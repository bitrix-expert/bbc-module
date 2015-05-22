<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Plugins\Elements;

use Bitrix\Iblock\InheritedProperty;
use Bitrix\Main\Page\Asset;
use Bex\Bbc\Plugin\Plugin;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 *
 * @todo Add .parameters.php
 */
class SeoPlugin extends Plugin
{
    protected function executeProlog()
    {
        if ($this->component->arParams['OG_TAGS_IMAGE'])
        {
            $this->addParamsSelected([$this->component->arParams['OG_TAGS_IMAGE']]);
        }
    }

    protected function executeMain()
    {
        if ($this->component->arParams['SECTION_CODE'] && !$this->component->arParams['SECTION_ID'])
        {
            $this->component->arParams['SECTION_ID'] = \CIBlockFindTools::GetSectionID(
                0,
                $this->component->arParams['SECTION_CODE'],
                []
            );
        }

        if ($this->component->arParams['ELEMENT_CODE'] && !$this->component->arParams['ELEMENT_ID'])
        {
            $this->component->arParams['ELEMENT_ID'] = \CIBlockFindTools::GetElementID(
                0,
                $this->component->arParams['ELEMENT_CODE'],
                $this->component->arParams['SECTION_ID'],
                $this->component->arParams['SECTION_CODE'],
                []
            );
        }

        $this->readInheritedProps();
        $this->readSectionParams();
        $this->readOgDatas();
    }

    protected function readInheritedProps()
    {
        if ($this->component->arParams['SET_SEO_TAGS'] !== 'Y' || !$this->component->arParams['IBLOCK_ID'])
        {
            if ($this->component->arParams['OG_TAGS_TITLE'] !== 'SEO_TITLE' && $this->component->arParams['OG_TAGS_DESCRIPTION'] !== 'SEO_DESCRIPTION')
            {
                return false;
            }
        }

        if ($this->component->arParams['ELEMENT_ID'])
        {
            $rsSeoValues = new InheritedProperty\ElementValues(
                $this->component->arParams['IBLOCK_ID'],
                $this->component->arParams['ELEMENT_ID']
            );

            $seoValues = $rsSeoValues->getValues();

            if (!$this->component->arResult['SEO_TAGS']['TITLE'])
            {
                $this->component->arResult['SEO_TAGS']['TITLE'] = $seoValues['ELEMENT_META_TITLE'];
            }

            if (!$this->component->arResult['SEO_TAGS']['DESCRIPTION'])
            {
                $this->component->arResult['SEO_TAGS']['DESCRIPTION'] = $seoValues['ELEMENT_META_DESCRIPTION'];
            }

            if (!$this->component->arResult['SEO_TAGS']['KEYWORDS'])
            {
                $this->component->arResult['SEO_TAGS']['KEYWORDS'] = $seoValues['ELEMENT_META_KEYWORDS'];
            }
        }
        elseif ($this->component->arParams['SECTION_ID'])
        {
            $rsSeoValues = new InheritedProperty\SectionValues(
                $this->component->arParams['IBLOCK_ID'],
                $this->component->arParams['SECTION_ID']
            );

            $seoValues = $rsSeoValues->getValues();

            if (!$this->component->arResult['SEO_TAGS']['TITLE'])
            {
                $this->component->arResult['SEO_TAGS']['TITLE'] = $seoValues['SECTION_META_TITLE'];
            }

            if (!$this->component->arResult['SEO_TAGS']['DESCRIPTION'])
            {
                $this->component->arResult['SEO_TAGS']['DESCRIPTION'] = $seoValues['SECTION_META_DESCRIPTION'];
            }

            if (!$this->component->arResult['SEO_TAGS']['KEYWORDS'])
            {
                $this->component->arResult['SEO_TAGS']['KEYWORDS'] = $seoValues['SECTION_META_KEYWORDS'];
            }
        }

        if (!empty($this->component->arResult['SEO_TAGS']) && is_array($this->component->arResult['SEO_TAGS']))
        {
            foreach ($this->component->arResult['SEO_TAGS'] as &$field)
            {
                $field = strip_tags($field);
            }

            unset ($field);
        }

        if (!empty($this->component->arResult['SEO_TAGS']))
        {
            $this->component->setResultCacheKeys(['SEO_TAGS']);
        }
    }

    protected function readSectionParams()
    {
        if ($this->component->arResult['IBLOCK_SECTION_ID'])
        {
            $this->component->arParams['SECTION_ID'] = $this->component->arResult['IBLOCK_SECTION_ID'];
        }

        if ($this->component->arParams['SECTION_ID'] > 0)
        {
            $this->component->arResult['SECTION'] = ['PATH' => []];

            $rsPath = \CIBlockSection::GetNavChain(
                $this->component->arParams['IBLOCK_ID'],
                $this->component->arParams['SECTION_ID']
            );

            $rsPath->SetUrlTemplates(
                '',
                $this->component->arParams['SECTION_URL'],
                $this->component->arParams['IBLOCK_URL']
            );

            while ($arPath = $rsPath->GetNext())
            {
                $ipropValues = new InheritedProperty\SectionValues(
                    $this->component->arParams['IBLOCK_ID'],
                    $arPath['ID']
                );

                $arPath['IPROPERTY_VALUES'] = $ipropValues->getValues();

                $this->component->arResult['SECTION']['PATH'][] = $arPath;
            }

            $ipropValues = new InheritedProperty\SectionValues(
                $this->component->arParams['IBLOCK_ID'],
                $this->component->arParams['SECTION_ID']
            );

            $this->component->arResult['IPROPERTY_VALUES'] = $ipropValues->getValues();
        }
        else
        {
            $this->component->arResult['SECTION'] = false;
        }

        $this->component->setResultCacheKeys(['SECTION']);
    }

    protected function readOgDatas()
    {
        global $APPLICATION;

        if (!$this->component->arResult['OG_TAGS']['TITLE'])
        {
            if ($this->component->arParams['OG_TAGS_TITLE'] === 'SEO_TITLE')
            {
                $this->component->arResult['OG_TAGS']['TITLE'] = strip_tags(
                    $this->component->arResult['SEO_TAGS']['TITLE']
                );
            }
            elseif ($this->component->arParams['OG_TAGS_TITLE'])
            {
                $this->component->arResult['OG_TAGS']['TITLE'] = strip_tags(
                    $this->component->arResult[$this->component->arParams['OG_TAGS_TITLE']]
                );
            }
        }

        if (!$this->component->arResult['OG_TAGS']['DESCRIPTION'])
        {
            if ($this->component->arParams['OG_TAGS_DESCRIPTION'] === 'SEO_DESCRIPTION')
            {
                $this->component->arResult['OG_TAGS']['DESCRIPTION'] = strip_tags(
                    $this->component->arResult['SEO_TAGS']['DESCRIPTION']
                );
            }
            elseif ($this->component->arParams['OG_TAGS_DESCRIPTION'])
            {
                $this->component->arResult['OG_TAGS']['DESCRIPTION'] = strip_tags(
                    $this->component->arResult[$this->component->arParams['OG_TAGS_DESCRIPTION']]
                );
            }
        }

        if (!$this->component->arResult['OG_TAGS']['IMAGE'] && $this->component->arParams['OG_TAGS_IMAGE'])
        {
            $file = \CFile::GetPath($this->component->arResult[$this->component->arParams['OG_TAGS_IMAGE']]);

            if ($file)
            {
                $this->component->arResult['OG_TAGS']['IMAGE'] = 'http://'.SITE_SERVER_NAME.$file;
            }
        }

        if ($this->component->arParams['OG_TAGS_URL'] === 'SHORT_LINK')
        {
            $this->component->arResult['OG_TAGS']['URL'] = $this->getShortLink($APPLICATION->GetCurPage());
        }

        if (!empty($this->component->arResult['OG_TAGS']) && is_array($this->component->arResult['OG_TAGS']))
        {
            foreach ($this->component->arResult['OG_TAGS'] as &$field)
            {
                $field = strip_tags($field);
            }

            unset($field);
        }

        if (!empty($this->component->arResult['OG_TAGS']))
        {
            $this->component->setResultCacheKeys(['OG_TAGS']);
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

        if ($this->component->arParams['SET_SEO_TAGS'] !== 'Y')
        {
            return false;
        }

        if ($this->component->arResult['SEO_TAGS']['TITLE'])
        {
            $APPLICATION->SetPageProperty('title', $this->component->arResult['SEO_TAGS']['TITLE']);
        }

        if ($this->component->arResult['SEO_TAGS']['DESCRIPTION'])
        {
            $APPLICATION->SetPageProperty('description', $this->component->arResult['SEO_TAGS']['DESCRIPTION']);
        }

        if ($this->component->arResult['SEO_TAGS']['KEYWORDS'])
        {
            $APPLICATION->SetPageProperty('keywords', $this->component->arResult['SEO_TAGS']['KEYWORDS']);
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
        if ($this->component->arResult['OG_TAGS']['TITLE'])
        {
            Asset::getInstance()->addString('<meta property="og:title" content="'.$this->component->arResult['OG_TAGS']['TITLE'].'" />', true);
        }

        if ($this->component->arResult['OG_TAGS']['DESCRIPTION'])
        {
            Asset::getInstance()->addString('<meta property="og:description" content="'.$this->component->arResult['OG_TAGS']['DESCRIPTION'].'" />', true);
        }

        if ($this->component->arResult['OG_TAGS']['URL'])
        {
            Asset::getInstance()->addString('<meta property="og:url" content="'.$this->component->arResult['OG_TAGS']['URL'].'" />', true);
        }

        if ($this->component->arResult['OG_TAGS']['IMAGE'])
        {
            Asset::getInstance()->addString('<meta property="og:image" content="'.$this->component->arResult['OG_TAGS']['IMAGE'].'" />', true);
        }
    }

    protected function setNavChain()
    {
        global $APPLICATION;

        if ($this->component->arParams['ADD_SECTIONS_CHAIN'] && is_array($this->component->arResult['SECTION']))
        {
            foreach ($this->component->arResult['SECTION']['PATH'] as $path)
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

        if ($this->component->arParams['ADD_ELEMENT_CHAIN'] === 'Y' && $this->component->arResult['NAME'])
        {
            $APPLICATION->AddChainItem($this->component->arResult['NAME']);
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

    protected function executeEpilog()
    {
        $this->setSeoTags();
        $this->setOgTags();
        $this->setNavChain();
    }
}