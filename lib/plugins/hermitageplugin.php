<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Plugins;

/**
 * Hermitage it is plugin, which show admin interface on public pages for editing content from info blocks.
 *
 * What would plugin showed the admin interface, after action must be:
 * - `IBLOCK_ID` and `SECTION_ID` in `arParams` property in the component class,
 * - `ID` in `arResult` property in the component class.
 *
 * @see https://www.1c-bitrix.ru/products/intranet/hermitage.php
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class HermitagePlugin extends Plugin
{
    public function action()
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
            return;
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
            Asset::getInstance()->addJs(BX_ROOT . '/js/main/utils.js');

            foreach ($buttons['intranet'] as $button)
            {
                $this->component->addEditButton($button);
            }
        }
    }

    public function afterAction()
    {
        $this->setEditButtons();
    }
}