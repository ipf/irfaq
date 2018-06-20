<?php

if (is_array($GLOBALS['TCA']['pages']['ctrl']['typeicon_classes'])) {
    $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-irfaq'] = 'tcarecords-pages-contains-irfaq';
}

// Adding sysfolder icon
$GLOBALS['TCA']['pages']['columns']['module']['config']['items']['irfaq']['0'] = 'LLL:EXT:irfaq/Resources/Private/Language/locallang_db.xlf:tx_irfaq.sysfolder';
$GLOBALS['TCA']['pages']['columns']['module']['config']['items']['irfaq']['1'] = 'irfaq';
