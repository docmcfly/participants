<?php
namespace Cylancer\Participants\Domain\Repository;

use Cylancer\Participants\Domain\PresentState;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Cylancer\Participants\Utility\Utility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use Cylancer\Participants\Domain\Model\Commitment;
use Cylancer\Participants\Domain\Model\PersonalDutyRosterGroupFilterSettings;
use Cylancer\Participants\Domain\Model\FrontendUser;
use Cylancer\Participants\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 C. Gogolin <service@cylancer.net>
 */
class CommitmentRepository extends Repository
{

    /**
     *
     * @param string $table
     * @return QueryBuilder
     */
    protected function getQueryBuilder(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

    // / --------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     *
     * @param FrontendUser $user
     * @param array $dutyRosterStrorageUids
     * @param int $planningStorageUid
     * @param array $personalDutyRosterGroups
     * @param PersonalDutyRosterGroupFilterSettings $personalDutyRosterFilterSettings
     * @param \DateTime $startMoment
     * @param bool $withCanceledEvents
     * @return \Cylancer\Participants\Domain\Model\Commitment[]
     */
    public function findCurrentEventCommitments(FrontendUser $user, array $dutyRosterStrorageUids, int $planningStorageUid, array $personalDutyRosterGroups, PersonalDutyRosterGroupFilterSettings $personalDutyRosterFilterSettings, \DateTime $startMoment, bool $withCanceledEvents = true)
    {
        if (empty($dutyRosterStrorageUids) || empty($personalDutyRosterGroups)) {
            return array();
        }
        $getUid = function ($object): int {
            return $object->getUid();
        };

        $qb = $this->getQueryBuilder('tx_participants_domain_model_commitment');

        // debug($hiddenTG);
        $qb->select('tx_participants_domain_model_commitment.uid', 'tx_participants_domain_model_event.date', 'tx_participants_domain_model_event.time')
            ->from('tx_participants_domain_model_commitment')
            ->join('tx_participants_domain_model_commitment', 'tx_participants_domain_model_event', 'tx_participants_domain_model_event', $qb->expr()
                ->andX($qb->expr()
                    ->eq('tx_participants_domain_model_commitment.event', $qb->quoteIdentifier('tx_participants_domain_model_event.uid')), $qb->expr()
                        ->in('tx_participants_domain_model_event.pid', $dutyRosterStrorageUids)))
            ->where($qb->expr()
                ->andX($qb->expr()
                    ->gte('tx_participants_domain_model_event.date', $qb->createNamedParameter($startMoment->format('Y-m-d'))), $qb->expr()
                        ->eq('tx_participants_domain_model_commitment.user', $qb->createNamedParameter($user->getUid())), $qb->expr()
                        ->eq('tx_participants_domain_model_commitment.pid', $planningStorageUid)))
            ->orderby('tx_participants_domain_model_event.date', 'ASC')
            ->addOrderby('tx_participants_domain_model_event.time', 'ASC')
            ->groupBy('tx_participants_domain_model_commitment.uid');

        if (!$withCanceledEvents) {
            $qb->andWhere($qb->expr()
                ->eq('tx_participants_domain_model_event.canceled', 0));
        }

        $s = $qb->execute();

        // debug($qb->getSQL());

        $return = array();

        while ($row = $s->fetchAssociative()) {
            // minute-by-minute calculation
            // /** @var \DateTime $eventStart */
            // if(empty($row['time'])){
            // $eventStart = \DateTime::createFromFormat('Y-m-d', $row['date']);
            // } else {
            // $eventStart = \DateTime::createFromFormat('Y-m-d H:i:s', $row['date'] . ' ' . $row['time']);
            // }
            // debug($startMoment);
            // debug($eventStart);

            // /** @var \DateTime $startMoment */
            // if ($startMoment->getTimestamp() < $eventStart->getTimestamp()) {
            // debug($row);
            /**
             *
             * @var FrontendUserGroup $ug
             * @var Commitment $c
             */
            $c = $this->findByUid($row['uid']);
            $g = array_unique(
                array_merge(
                    array_map($getUid, $c->getEvent()
                        ->getUsergroups()
                        ->toArray()),
                    array_map($getUid, $c->getEvent()
                        ->getEventType()
                        ->getUsergroups()
                        ->toArray())
                )
            );
            $canDisplayBecause = array_intersect($g, $personalDutyRosterGroups);

            $f = false;
            foreach ($canDisplayBecause as $uid) {
                $existsOpt = $personalDutyRosterFilterSettings->exists($uid);
                if (!$existsOpt || ($existsOpt && $personalDutyRosterFilterSettings->get($uid)->getVisible())) {
                    $f = true;
                    break;
                }
            }
            if ($f) {
                $return[] = $c;
            }
            // }
        }
        return $return;
    }

    /**
     * EVENT
     *
     * @param int $userUid
     * @param int $commitmentStorageUid
     * @param array $eventStorageUids
     * @return int[]
     */
    public function findMissingCommitmentsOf(int $userUid, int $commitmentStorageUid, array $eventStorageUids): array
    {
        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval('P1D'));

        $qb = $this->getQueryBuilder('tx_participants_domain_model_event');
        $qb->select('tx_participants_domain_model_event.uid', 'tx_participants_domain_model_commitment.pid')
            ->from('tx_participants_domain_model_event')
            ->leftJoin('tx_participants_domain_model_event', 'tx_participants_domain_model_commitment', 'tx_participants_domain_model_commitment', $qb->expr()
                ->andx($qb->expr()
                    ->eq('tx_participants_domain_model_commitment.event', $qb->quoteIdentifier('tx_participants_domain_model_event.uid')), $qb->expr()
                        ->eq('tx_participants_domain_model_commitment.user', $qb->createNamedParameter($userUid)), $qb->expr()
                        ->eq('tx_participants_domain_model_commitment.pid', $qb->createNamedParameter($commitmentStorageUid))))
            ->where($qb->expr()
                ->isNull('tx_participants_domain_model_commitment.uid'))
            ->andWhere($qb->expr()
                ->in('tx_participants_domain_model_event.pid', $eventStorageUids))
            ->andWhere($qb->expr()
                ->gt('tx_participants_domain_model_event.date', $yesterday->format('y-m-d')));

        // debug($qb->getSQL());
        $s = $qb->execute();
        $return = array();
        while ($row = $s->fetchAssociative()) {
            $return[] = $row['uid'];
        }
        return $return;
    }

    /**
     * EVENT
     *
     * @param int $userUid
     * @param int $commitmentStorageUid
     * @param int[] $eventStorageUids
     * @return array
     */
    public function findExistsFutureCommitments(int $userUid, int $commitmentStorageUid, array $eventStorageUids): array
    {
        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval('P1D'));

        $qb = $this->getQueryBuilder('tx_participants_domain_model_event');
        $r = $qb->getRestrictions();
        $r->removeByType(DeletedRestriction::class);
        $r->removeByType(HiddenRestriction::class);

        $qb->select('tx_participants_domain_model_commitment.uid AS cuid', 'tx_participants_domain_model_event.uid AS euid')
            ->from('tx_participants_domain_model_event')
            ->join('tx_participants_domain_model_event', 'tx_participants_domain_model_commitment', 'tx_participants_domain_model_commitment', $qb->expr()
                ->andx($qb->expr()
                    ->eq('tx_participants_domain_model_commitment.event', $qb->quoteIdentifier('tx_participants_domain_model_event.uid')), $qb->expr()
                        ->eq('tx_participants_domain_model_commitment.user', $qb->createNamedParameter($userUid))))
            ->where($qb->expr()
                ->in('tx_participants_domain_model_event.pid', $eventStorageUids))
            ->andWhere($qb->expr()
                ->gt('tx_participants_domain_model_event.date', $yesterday->format('y-m-d')))
            ->andWhere($qb->expr()
                ->eq('tx_participants_domain_model_commitment.pid', $commitmentStorageUid));

        // debug($qb->getSql());
        $s = $qb->execute();
        $return = array();
        while ($row = $s->fetchAssociative()) {
            $return[] = [
                'commitment' => $row['cuid'],
                'event' => $row['euid']
            ];
            // debug($row);
        }

        // debug($return);
        return $return;
    }

    /**
     *
     * @param int $planningStorageUid
     * @param int $eventUid
     * @return array
     */
    public function getEventCommitmentCounts(int $planningStorageUid, int $eventUid = null): array
    {
        $qb = $this->getQueryBuilder('tx_participants_domain_model_commitment');
        $planningStorageUidTerm = $qb->expr()->eq('tx_participants_domain_model_commitment.pid', $planningStorageUid);
        $eventTerm = $eventUid == null ? '' : $qb->expr()->eq('tx_participants_domain_model_commitment.event', $eventUid);

        // debug($pagesTerm);
        $qb->select('tx_participants_domain_model_commitment.event')
            ->addSelectLiteral('sum( if(present_default = true,1,0)) AS present_default_count')
            ->addSelectLiteral('sum(if(present = true, 1,0)) AS present_count ')
            ->join('tx_participants_domain_model_commitment', 'fe_users', 'fe_users', $qb->expr()
                ->andX($qb->expr()
                    ->eq('tx_participants_domain_model_commitment.user', $qb->quoteIdentifier('fe_users.uid')), $qb->expr()
                        ->neq('fe_users.disable', $qb->createNamedParameter(1)), $qb->expr()
                        ->neq('fe_users.deleted', $qb->createNamedParameter(1))))
            ->from('tx_participants_domain_model_commitment')
            ->where($planningStorageUidTerm, $eventTerm)
            ->groupBy('tx_participants_domain_model_commitment.event');

        $s = $qb->execute();
        $counts = array();
        // $counts['sql'] = $qb->getSql();
        while ($row = $s->fetchAssociative()) {
            $counts[intval($row['event'])] = Utility::calculatePresentDatas($row['present_count'], $row['present_default_count']);
        }

        // debug($counts);
        return $eventUid == null ? $counts : $counts[$eventUid];
    }


    /**
     *
     * @param array $storageUids
     * @param int $eventUid
     * @return array
     */
    public function getEventCommitments(int $presentState, int $pidList, int $eventUid = null, bool $userIsScheduled = true): array
    {
        $qb = $this->getQueryBuilder('tx_participants_domain_model_commitment');

        $pagesTerm = '';
        if ($pidList != null) {
            $pagesTerm = $qb->expr()->in('tx_participants_domain_model_commitment.pid', $pidList);
        }
        $eventTerm = $eventUid == null ? '' : $qb->expr()->eq('tx_participants_domain_model_commitment.event', $eventUid);
        $undecided = $qb->expr()->eq('tx_participants_domain_model_commitment.present', $qb->createNamedParameter($presentState));
        $isScheduled = $qb->expr()->eq('tx_participants_domain_model_commitment.present_default', $qb->createNamedParameter($userIsScheduled));

        // debug($pagesTerm);
        $qb->select('tx_participants_domain_model_commitment.event', 'fe_users.uid', 'fe_users.first_name', 'fe_users.last_name', 'fe_users.currently_off_duty')
            ->join('tx_participants_domain_model_commitment', 'fe_users', 'fe_users', $qb->expr()
                ->andX($qb->expr()
                    ->eq('tx_participants_domain_model_commitment.user', $qb->quoteIdentifier('fe_users.uid')), $qb->expr()
                        ->neq('fe_users.disable', $qb->createNamedParameter(1)), $qb->expr()
                        ->neq('fe_users.deleted', $qb->createNamedParameter(1))))
            ->from('tx_participants_domain_model_commitment')
            ->where($pagesTerm, $eventTerm, $isScheduled, $undecided)
            ->addOrderBy('fe_users.last_name', 'ASC')
            ->addOrderBy('fe_users.first_name', 'ASC');
        $users = array();
        $sql = $qb->getSql();
        $s = $qb->execute();
        while ($row = $s->fetchAssociative()) {
            $users[$row['uid']] = [
                'last_name' => $row['last_name'],
                'first_name' => $row['first_name'],
                'currently_off_duty' => $row['currently_off_duty']
            ];
        }
      //   $users['sql'] = $sql;
        // debug(json_encode($counts));
        return $users;
    }

    /**
     * EVENT
     *
     * @param int $userId
     * @param \DateTime $from
     * @param \DateTime $until
     *
     */
    public function dropout(int $userId, \DateTime $from, \DateTime $until): void
    {
        $qb = $this->getQueryBuilder('tx_participants_domain_model_commitment');
        $qb->select('tx_participants_domain_model_commitment.uid')
            ->from('tx_participants_domain_model_commitment')
            ->join('tx_participants_domain_model_commitment', 'tx_participants_domain_model_event', 'tx_participants_domain_model_event', $qb->expr()
                ->andX($qb->expr()
                    ->eq('tx_participants_domain_model_commitment.event', $qb->quoteIdentifier('tx_participants_domain_model_event.uid')), $qb->expr()
                        ->gte('tx_participants_domain_model_event.date', $qb->createNamedParameter($from->format('Y-m-d'))), $qb->expr()
                        ->lte('tx_participants_domain_model_event.date', $qb->createNamedParameter($until->format('Y-m-d')))))
            ->where($qb->expr()
                ->eq('tx_participants_domain_model_commitment.user', $userId));
        // debug($sql = $qb->getSql());
        $s = $qb->execute();

        while ($row = $s->fetch()) {
            // debug($row);
            $u = $qb->update('tx_participants_domain_model_commitment')
                ->set('present', 0)
                ->where($qb->expr()
                    ->eq('tx_participants_domain_model_commitment.uid', $row['uid']));
            // debug($u->getSQL());
            $u->execute();
        }
    }
}