<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Dmitry Dulepov <dmitry@typo3.org>
 *  (c) 2009-2017 Leonie Philine Bitto (extensions@netcreators.nl)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Updates the extension from older versions to this one.
 *
 * @author    Dmitry Dulepov <dmitry@typo3.org>
 */
class ext_update
{
    /** @var \TYPO3\CMS\Lang\LanguageService Language support */
    protected $lang;

    /** Defines new sheets and fields inside them */
    protected $fieldSet = [
        'sCATEGORIES' => ['categoryMode', 'categorySelection'],
        'sSEARCH' => ['searchPid', 'emptySearchAtStart'],
    ];

    /**
     * Shows form and/or runs the update process.
     *
     * @return string Output
     */
    public function main()
    {
        $this->lang = $this->getLanguageService();
        $this->lang->init($GLOBALS['BE_USER']->uc['lang']);
        $this->lang->includeLLFile('EXT:irfaq/Resources/Private/Language/locallang_update.xlf');

        return $_POST['run'] ? $this->runConversion() : $this->showForm();
    }

    /**
     * Checks if "UPDATE!" should be shown at all. In out case it will be shown
     * only if there are irfaq records in the system.
     *
     * @return bool true if script should be displayed
     */
    public function access()
    {
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tt_content');

        $results = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where($queryBuilder->expr()->eq('list_type', $queryBuilder->quote('irfaq_pi1')))
            ->execute()
            ->fetchAll();

        return count($results) > 0;
    }

    /**
     * Shows form to update irfaq.
     *
     * @return string Generated form
     */
    public function showForm()
    {
        $content = '<p>'.$this->lang->getLL('form_intro').'</p>'.
            '<form action="'.htmlspecialchars(GeneralUtility::linkThisScript()).'" method="post">'.
            '<input type="hidden" name="CMD[showExt]" value="irfaq" />'.
            '<input type="hidden" name="SET[singleDetails]" value="updateModule" />'.
            '<input type="checkbox" name="replaceEmpty" value="1" />'.
            $this->lang->getLL('replace_empty').'<br />'.
            '<input type="submit" name="run" value="'.
            $this->lang->getLL('submit_button').'" /></form>';

        return $content;
    }

    /**
     * Runs conversion procedure.
     *
     * @return string Generated content
     */
    public function runConversion()
    {
        $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tt_content');

        $res = $queryBuilder
            ->select(['uid', 'pid', 'pi_flexform'])
            ->from('tt_content')
            ->where($queryBuilder->expr()->eq('list_type', $queryBuilder->quote('irfaq_pi1')))
            ->execute()
            ->fetchAll();

        $content = '';

        $results = count($res);
        $converted = 0;
        $data = [];
        $pidList = [];
        $replaceEmpty = intval(GeneralUtility::_GP('replaceEmpty'));

        /** @var \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools $flexformtools */
        $flexformtools = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class);

        $GLOBALS['TYPO3_CONF_VARS']['BE']['compactFlexFormXML'] = true;
        $GLOBALS['TYPO3_CONF_VARS']['BE']['niceFlexFormXMLtags'] = true;

        // Walk all rows
        foreach ($res as $row) {
            $ffArray = GeneralUtility::xml2array($row['pi_flexform']);
            $modified = false;
            if (is_array($ffArray) && isset($ffArray['data']['sDEF'])) {
                foreach ($ffArray['data']['sDEF'] as $sLang => $sLdata) {
                    foreach ($this->fieldSet as $sheet => $fieldList) {
                        foreach ($fieldList as $field) {
                            if (isset($ffArray['data']['sDEF'][$sLang][$field]) &&
                                isset($ffArray['data']['sDEF'][$sLang][$field]['vDEF']) &&
                                strlen($ffArray['data']['sDEF'][$sLang][$field]['vDEF']) > 0 &&
                                (!isset($ffArray['data'][$sheet][$sLang][$field]) ||
                                    !isset($ffArray['data'][$sheet][$sLang][$field]['vDEF']) ||
                                    ($replaceEmpty && 0 == strlen($ffArray['data'][$sheet][$sLang][$field]['vDEF'])))
                            ) {
                                $ffArray['data'][$sheet][$sLang][$field]['vDEF'] =
                                    $ffArray['data']['sDEF'][$sLang][$field]['vDEF'];
                                if ($row['pid'] > 0) {
                                    $pidList[$row['pid']] = $row['pid'];
                                }
                                $modified = true;
                            }
                        }
                    }
                }
            }
            if ($modified) {
                // Assemble data back
                $data['tt_content'][$row['uid']] = [
                    'pi_flexform' => $flexformtools->flexArray2Xml($ffArray),
                ];
                ++$converted;
            }
        }

        if ($converted > 0) {
            // Update data
            /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
            $dataHandler = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
            $dataHandler->start($data, null);
            $dataHandler->process_datamap();
            if (count($dataHandler->errorLog) > 0) {
                $content .= '<p>'.$this->lang->getLL('errors').'</p><ul><li>'.
                    implode('</li><li>', $dataHandler->errorLog).'</li></ul>';
            }
            // Clear cache
            foreach ($pidList as $pid) {
                $dataHandler->clear_cacheCmd($pid);
            }
        }

        return '<p>'.sprintf($this->lang->getLL('result'), $results, $converted).'</p>';
    }

    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
