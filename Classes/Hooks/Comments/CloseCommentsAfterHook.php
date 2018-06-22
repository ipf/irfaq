<?php

namespace Netcreators\Irfaq\Hooks\Comments;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Dmitry Dulepov (dmitry@typo3.org)
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Commenting system hook. Hook receives the following in <code>$params</code>:
 * <ul>
 *    <li><code>uid</code> - uid of the item</li>
 * </ul>
 * Hook returns time when commenting should be stopped. Thus 0 disables commenting and
 * <code>PHP_INT_MAX</code> means there is no limit.
 *
 * @author Dmitry Dulepov <dmitry@typo3.org>
 */
class CloseCommentsAfterHook
{
    /**
     * Provides comment closing date to comments extension for tt_news items.
     *
     * @param array            $params Parameters to the function
     * @param \tx_comments_pi1 $pObj   Parent object
     *
     * @return bool|int
     */
    public function irfaqHook(&$params, &$pObj)
    {
        return 'tx_irfaq_q' !== $params['table'] ? false : $this->getCloseTime(
            'tx_irfaq_q',
            $params['uid'],
            $pObj->cObj
        );
    }

    /**
     * Gets closing time from a record.
     *
     * @param string                                                  $table Table name
     * @param int                                                     $uid   UID of the record
     * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $cObj  COBJECT
     *
     * @return int Closing timestamp
     */
    public function getCloseTime($table, $uid, &$cObj)
    {
        $result = 0;
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $recs = $queryBuilder
            ->select(['disable_comments', 'comments_closetime'])
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', (int) $uid))
            ->execute()
            ->fetchAll();

        if (count($recs)) {
            $result = $recs[0]['disable_comments'] ? 0 :
                ($recs[0]['comments_closetime'] ? $recs[0]['comments_closetime'] : PHP_INT_MAX);
        }

        return $result;
    }
}
