<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Plugins\Elements;

use Bex\AdvancedComponent\Plugin;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class HermitagePlugin extends Plugin
{
    public function executeMain()
    {
        if ($this->component->arParams['SECTION_CODE'] && !$this->component->arParams['SECTION_ID'])
        {
            $this->component->arParams['SECTION_ID'] = \CIBlockFindTools::GetSectionID(
                0,
                $this->component->arParams['SECTION_CODE'],
                []
            );
        }
    }

    /**
     * Add to page buttons for edit elements and sections of info-block
     */
    protected function setEditButtons()
    {
        global $APPLICATION;

        if (!$APPLICATION->GetShowIncludeAreas())
        {
            return false;
        }

        $buttons = \CIBlock::GetPanelButtons(
            $this->component->arParams['IBLOCK_ID'],
            $this->component->arResult['ID'],
            $this->component->arParams['SECTION_ID'],
            []
        );

        $this->component->addIncludeAreaIcons(\CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));

        if (is_array($buttons['intranet']))
        {
            Asset::getInstance()->addJs(BX_ROOT.'/js/main/utils.js');

            foreach ($buttons['intranet'] as $button)
            {
                $this->component->addEditButton($button);
            }
        }
    }

    public function executeEpilog()
    {
        $this->setEditButtons();
    }
}