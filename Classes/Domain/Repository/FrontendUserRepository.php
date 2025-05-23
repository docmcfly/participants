<?php
declare(strict_types=1);
namespace Cylancer\Participants\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 *
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 *         
 */
class FrontendUserRepository extends Repository
{

    protected function getQueryBuilder(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

    /**
     *
     * @param string $userGroups
     * @return QueryResultInterface|array
     */
    public function findByUserGroups(string $userGroups = ''): array
    {
        if (empty(trim($userGroups))) {
            return [];
        }

        $this->createQuery()->getQuerySettings();

        $qb = $this->getQueryBuilder('fe_users');
        $qb->select('uid')->from('fe_users');

        foreach (GeneralUtility::intExplode(',', $userGroups, TRUE) as $ug) {
            $qb->orWhere($qb->expr()
                ->inSet('usergroup', strval($ug)));
        }
        $qb->orderBy('last_name')->addOrderBy('first_name');

        //  debug($qb->getSql());

        $s = $qb->executeQuery();
        $return = array();
        while ($row = $s->fetchAssociative()) {
            $return[] = $this->findByUid($row['uid']);
        }

        return $return;
    }
}

