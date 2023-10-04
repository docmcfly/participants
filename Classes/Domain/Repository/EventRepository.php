<?php
namespace Cylancer\Participants\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use Cylancer\Participants\Domain\PublicOption;
use TYPO3\CMS\Extbase\Persistence\Repository;
use Cylancer\Participants\Domain\Model\Event;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 *
 * The repository for events
 */
class EventRepository extends Repository
{

    protected $defaultOrderings = array(
        'date' => QueryInterface::ORDER_ASCENDING,
        'time' => QueryInterface::ORDER_ASCENDING
    );

    const UNLIMITED = -1;

    /**
     *
     * @param string $table
     * @return QueryBuilder
     */
    protected function getQueryBuilder(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

    /**
     *
     * @return Event[]
     */
    public function findTomorrowsEvents(): array
    {
        $tmp = new \DateTime();
        $tmp->modify('+1 day');
        $tmp->setTimestamp('UTC');
        $tomorrow = $tmp->getTimestamp();

        $qb = $this->getQueryBuilder('tx_participants_domain_model_event');
        $qb->select('tx_participants_domain_model_event.uid')
            ->from('tx_participants_domain_model_event')
            ->where($qb->expr()
                ->eq('begin_date', $qb->createNamedParameter($tomorrow)))
            ->andWhere($qb->expr()
                ->eq('canceled', 0))
            ->orderBy('begin_date', QueryInterface::ORDER_ASCENDING)
            ->addOrderBy('begin_time', QueryInterface::ORDER_ASCENDING);

        // debug($qb->getSql());
        $s = $qb->execute();
        $return = array();

        while ($row = $s->fetchAssociative()) {
            $return[] = $this->findByUid($row['uid']);
        }
        // debug($return);
        return $return;
    }

    public function findPublicEvents(int $limit = EventRepository::UNLIMITED, array $storageUids = null, bool $inclusiveCanceledEvents = true, bool $startWithToday = false): array
    {
        $qb = $this->getQueryBuilder('tx_participants_domain_model_event');
        $qb->select('tx_participants_domain_model_event.uid')
            ->from('tx_participants_domain_model_event')
            ->join('tx_participants_domain_model_event', 'tx_participants_domain_model_eventtype', 'tx_participants_domain_model_eventtype', $qb->expr()
                ->eq('tx_participants_domain_model_event.event_type', $qb->quoteIdentifier('tx_participants_domain_model_eventtype.uid')))
            ->where($qb->expr()
                ->orX($qb->expr()
                    ->eq('tx_participants_domain_model_event.public', PublicOption::PUBLIC ), $qb->expr()
                        ->andX($qb->expr()
                            ->eq('tx_participants_domain_model_event.public', PublicOption::INHERITED), $qb->expr()
                                ->eq('tx_participants_domain_model_eventtype.public', PublicOption::PUBLIC ))))
            ->orderBy('begin_date', QueryInterface::ORDER_ASCENDING)
            ->addOrderBy('begin_time', QueryInterface::ORDER_ASCENDING);

        if ($storageUids == null) {
            $qb->andWhere($qb->expr()
                ->in('tx_participants_domain_model_event.pid', $this->createQuery()
                    ->getQuerySettings()
                    ->getStoragePageIds()));
        } else {
            $qb->andWhere($qb->expr()
                ->in('tx_participants_domain_model_event.pid', $storageUids));
        }

        if ($limit != EventRepository::UNLIMITED) {
            $qb->setMaxResults($limit);
        }

        if (!$inclusiveCanceledEvents) {
            $qb->andWhere($qb->expr()
                ->eq('tx_participants_domain_model_event.canceled', 0));
        }
        if ($startWithToday) {
            $qb->andWhere($qb->expr()
                ->gte('tx_participants_domain_model_event.begin_date', time()));
        }
        // debug($qb->getSql());
        $s = $qb->execute();
        $return = array();

        while ($row = $s->fetchAssociative()) {
            $return[] = $this->findByUid($row['uid']);
        }
        return $return;
    }


    public function findEventsAt(array $storageUids, int $from, int $until, int $visibility, int $limit = EventRepository::UNLIMITED, bool $inclusiveCanceledEvents = false): array
    {
        $qb = $this->getQueryBuilder('tx_participants_domain_model_event');

        switch ($visibility) {
            case PublicOption::PUBLIC:
            case PublicOption::INTERNAL:
                $visibilityRule = $qb->expr()->orX($qb->expr()
                    ->eq('tx_participants_domain_model_event.public', PublicOption::PUBLIC ), $qb->expr()
                        ->andX($qb->expr()
                            ->eq('tx_participants_domain_model_event.public', PublicOption::INHERITED), $qb->expr()
                                ->eq('tx_participants_domain_model_eventtype.public', PublicOption::PUBLIC )));

                break;
            default:
                $visibilityRule = null;
                break;
        }

        $qb = $this->getQueryBuilder('tx_participants_domain_model_event');
        $qb->select('tx_participants_domain_model_event.uid')
            ->from('tx_participants_domain_model_event')
            ->join('tx_participants_domain_model_event', 'tx_participants_domain_model_eventtype', 'tx_participants_domain_model_eventtype', $qb->expr()
                ->eq('tx_participants_domain_model_event.event_type', $qb->quoteIdentifier('tx_participants_domain_model_eventtype.uid')))
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->gte('begin_date', $qb->createNamedParameter($from)),
                    $qb->expr()->lt('begin_date', $qb->createNamedParameter($until))
                )
            )
            ->orderBy('begin_date', QueryInterface::ORDER_ASCENDING)
            ->addOrderBy('begin_date', QueryInterface::ORDER_ASCENDING);
        if ($visibilityRule != null) {
            $qb->andWhere($visibilityRule);
        }

        if ($storageUids == null) {
            $qb->andWhere($qb->expr()
                ->in('tx_participants_domain_model_event.pid', $this->createQuery()
                    ->getQuerySettings()
                    ->getStoragePageIds()));
        } else {
            $qb->andWhere($qb->expr()
                ->in('tx_participants_domain_model_event.pid', $storageUids));
        }

        if ($limit != EventRepository::UNLIMITED) {
            $qb->setMaxResults($limit);
        }

        if (!$inclusiveCanceledEvents) {
            $qb->andWhere($qb->expr()
                ->eq('tx_participants_domain_model_event.canceled', 0));
        }

        $s = $qb->execute();
        $return = [];
        if (1 == 0) { // add debug infos
            $debug = []; 
            $debug['sql'] = $qb->getSql();
            $debug['from'] = $from;
            $debug['until'] = $until;
            $return['debug'] = $debug;
        }
        $data = [];
        while ($row = $s->fetchAssociative()) {
            /** @var Event $e */
            $e = $this->findByUid($row['uid']);
            $tmp = [];
            $tmp['description'] = htmlspecialchars($e->getEventType()->getTitle() .' ('.self::formatDateUTC( $e->getBeginDate(), 'd.m.Y').')');
            $tmp['title'] =  htmlspecialchars($e->getEventType()->getTitle());
            $tmp['begin_date'] =  htmlspecialchars(self::formatDateUTC( $e->getBeginDate(), 'd.m.Y'));
            $data[] = $tmp;
        }
        $return['data'] = $data;
        return $return;
    }



    private static function formatDateUTC(int $timestamp, string $format): string {
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        $date->setTimezone(new \DateTimeZone('UTC'));
        return $date->format($format); 


    }

}