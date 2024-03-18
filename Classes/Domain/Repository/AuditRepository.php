<?php

namespace SPT\SptTypo3audit\Domain\Repository;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;


/***
 *
 * This file is part of the "TYPO3 Audit" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Arun <arun@spawoz.com>, Spawoz
 *
 ***/

/**
 * The repository for Audits
 */
class AuditRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    //To get the total number of pages
    public function getPagesCount()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages')->createQueryBuilder();
        $pages = $queryBuilder->count('uid')->from('pages')->where(
            $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter('0'))
        )->execute()->fetchColumn(0);
        return $pages;
    }

    //To get the count of domains
    public function getDomainsCount($sysDomain)
    {
        $queryBuilder_sys_domain = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($sysDomain)->createQueryBuilder();
        $sys_domain = $queryBuilder_sys_domain->count('pid')->from($sysDomain)->execute()->fetchColumn(0);
        return $sys_domain;
    }

    //To get the count of languages
    public function getLanguageCount()
    {
        $queryBuilder_sys_language = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_language')->createQueryBuilder();
        $language = $queryBuilder_sys_language->count('pid')->from('sys_language')->execute()->fetchColumn(0);
        return $language;
    }

}
