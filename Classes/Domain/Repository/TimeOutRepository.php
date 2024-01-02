<?php
namespace Cylancer\Participants\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Clemens Gogolin <service@cylancer.net>
 *
 * The repository for time out entries.
 *
 * @package Cylancer\Participants\Domain\Repository
 */
class TimeOutRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     *
     * @return QueryResultInterface|array
     */
    public function getTimeOuts()
    {
        $today = date('Y-m-d');
        $q = $this->createQuery();
        $q->matching($q->logicalAnd([
            $q->lessThanOrEqual('from', $today),
            $q->greaterThanOrEqual('until', $today)
        ]));
        // $queryParser = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);
        // debug($queryParser->convertQueryToDoctrineQueryBuilder($q)->getSQL());
        return $q->execute();
    }
}
