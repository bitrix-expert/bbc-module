<?php

if (!check_bitrix_sessid())
{
    return false;
}

if (version_compare(SM_VERSION, '15.0.2') < 0)
{
    CAdminMessage::ShowMessage([
            'TYPE' => 'ERROR',
            'MESSAGE' => GetMessage('BBC_INSTALL_ERROR_BITRIX_VERSION_TITLE'),
            'DETAILS' => GetMessage('BBC_INSTALL_ERROR_BITRIX_VERSION_MESSAGE'),
            'HTML' => true
        ]);
    ?>
    <div style="margin-top: 20px;">
        <input onclick="location.href='update_system.php?lang=<?=LANGUAGE_ID?>'" type="submit" class="adm-btn-save"
               value="<?=GetMessage('BBC_INSTALL_LINK_BITRIX_UPDATE')?>">
        <input onclick="location.href='<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>'" type="submit"
               value="<?=GetMessage('BBC_INSTALL_LINK_BACK_SOLUTIONS')?>">
    </div>
<?
}
else
{
    RegisterModule('bex.bbc');
    CAdminMessage::ShowNote(GetMessage('BBC_INSTALL_COMPLETE_TITLE'));
    echo GetMessage('BBC_INSTALL_COMPLETE_MESSAGE');
    ?>
    <div style="margin-top: 20px;">
        <input onclick="location.href='<?=$APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>'" type="submit"
               value="<?=GetMessage('BBC_INSTALL_LINK_BACK_SOLUTIONS')?>"/>
    </div>
<?
}