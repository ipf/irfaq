<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_irfaq_q');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_irfaq_cat');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_irfaq_expert');

if (TYPO3_MODE === 'BE') {
    $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses'][\Netcreators\Irfaq\System\Backend\WizardIcon::class] =
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('irfaq')
            .'Classes/System/Backend/WizardIcon.php';

    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'tcarecords-pages-contains-irfaq',
        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        ['source' => 'EXT:irfaq/Resources/Public/Icons/icon_tx_irfaq_sysfolder.gif']
    );
}
