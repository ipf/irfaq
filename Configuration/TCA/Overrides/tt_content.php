<?php

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['irfaq_pi1'] = 'layout,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['irfaq_pi1'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    ['LLL:EXT:irfaq/Resources/Private/Language/locallang_db.xlf:tt_content.list_type_pi1', 'irfaq_pi1'],
    'list_type'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'irfaq_pi1',
    'FILE:EXT:irfaq/Configuration/FlexForms/flexform_ds.xml'
);
