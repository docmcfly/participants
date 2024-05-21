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
 * (c) 2024 C.Gogolin <service@cylancer.net>
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
        $tomorrow = $tmp->format('Y-m-d');

        $qb = $this->getQueryBuilder('tx_participants_domain_model_event');
        $qb->select('tx_participants_domain_model_event.uid')
            ->from('tx_participants_domain_model_event')
            ->where($qb->expr()
                ->eq('date', $qb->expr()
                    ->literal($tomorrow)))
            ->andWhere($qb->expr()
                ->eq('canceled', 0))
            ->orderBy('date', QueryInterface::ORDER_ASCENDING)
            ->addOrderBy('time', QueryInterface::ORDER_ASCENDING);

        // debug($qb->getSql());
        $s = $qb->executeQuery();
        $return = array();

        while ($row = $s->fetch()) {
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
                ->or($qb->expr()
                    ->eq('tx_participants_domain_model_event.public', PublicOption::PUBLIC ), $qb->expr()
                        ->and($qb->expr()
                            ->eq('tx_participants_domain_model_event.public', PublicOption::INHERITED), $qb->expr()
                                ->eq('tx_participants_domain_model_eventtype.public', PublicOption::PUBLIC ))))
            ->orderBy('date', QueryInterface::ORDER_ASCENDING)
            ->addOrderBy('time', QueryInterface::ORDER_ASCENDING);

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
                ->gte('tx_participants_domain_model_event.date', $qb->createNamedParameter(date('Y-m-d'))));
        }
        // debug($qb->getSql());
        $s = $qb->executeQuery();
        $return = array();

        while ($row = $s->fetchAssociative()) {
            $return[] = $this->findByUid($row['uid']);
        }
        return $return;
    }



    public function findEventsAt(\DateTime $from, \DateTime $until, int $visibility = PublicOption::ALL, int $limit = EventRepository::UNLIMITED, bool $inclusiveCanceledEvents = false): array
    {
        $qb = $this->getQueryBuilder('tx_participants_domain_model_event');

        switch ($visibility) {
            case PublicOption::PUBLIC:
            case PublicOption::INTERNAL:
                $visibilityRule = $qb->expr()->or($qb->expr()
                    ->eq('tx_participants_domain_model_event.public', PublicOption::PUBLIC ), $qb->expr()
                        ->and($qb->expr()
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
                $qb->expr()->and(
                    $qb->expr()->gte('date', $qb->createNamedParameter($from->format('Y-m-d'))),
                    $qb->expr()->lte('date', $qb->createNamedParameter($until->format('Y-m-d')))
                )
            )
            ->orderBy('date', QueryInterface::ORDER_ASCENDING)
            ->addOrderBy('time', QueryInterface::ORDER_ASCENDING);
        if ($visibilityRule != null) {
            $qb->andWhere($visibilityRule);
        }

        if ($limit != EventRepository::UNLIMITED) {
            $qb->setMaxResults($limit);
        }

        $qb->andWhere($qb->expr()
            ->in('tx_participants_domain_model_event.pid', $this->createQuery()
                ->getQuerySettings()
                ->getStoragePageIds()));

        if (!$inclusiveCanceledEvents) {
            $qb->andWhere($qb->expr()
                ->eq('tx_participants_domain_model_event.canceled', 0));
        }

        $s = $qb->executeQuery();
      
        $return = [];
        if (1 == 0) { // add debug infos
            $debug = [];
            $debug['sql'] = $qb->getSql();
            $debug['from'] = $from->format('Y-m-d');
            $debug['until'] = $until->format('Y-m-d');
            $return['debug'] = $debug;
        }
        $data = [];
        while ($row = $s->fetchAssociative()) {
            /** @var Event $e */
            $e = $this->findByUid($row['uid']);
            $tmp = [];
            $tmp['description'] = htmlspecialchars($e->getEventType()->getTitle() . ' (' . $e->getDate()->format('d.m.Y') . ')');
            $tmp['title'] = htmlspecialchars($e->getEventType()->getTitle());
            $tmp['begin_date'] = htmlspecialchars($e->getDate()->format('d.m.Y'));
            $data[] = $tmp;
        }
        $return['data'] = $data;
        return $return;
    }

}