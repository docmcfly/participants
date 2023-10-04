<?php
namespace Cylancer\Participants\Controller;

use Cylancer\Participants\Domain\Model\Event;
use Cylancer\Participants\Domain\PublicOption;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Cylancer\Participants\Domain\Repository\EventRepository;

/**
 *
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 *
 * @package Cylancer\Participants\Controller
 */
class DutyRosterController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    const LIST_TYPE = 'participants_dutyroster';

    const DATE_PATTERN = '/^\d{4}-(((0[13578]|1[02])-(([012]\d)|3[01]))|((0[469]|11)-(([012]\d)|30))|02-[012]\d)$/m';

    /**
     *
     * @var EventRepository eventRepository
     */
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     *
     * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array $events
     * @return array
     */
    private function prepareEvents(array $events)
    {
        $now = time();
        $c = count($events);

        if ($c == 0) {
            $afterNow = -1;
        } else {
            /** @var Event $firstEvent */
            $firstEvent = $events[0];

            /** @var Event $firstEvent */
            $lastEvent = $events[count($events) - 1];
            if ($lastEvent->getBeginTimeStamp() < $now) {
                $afterNow = count($events) * (-1);
            } else {
                $afterNow = -1;
                $c = 0;

                /**
                 *
                 * @var Event $e
                 */
                foreach ($events as $e) {
                    if ($afterNow == -1 && $e->getBeginTimeStamp() > $now) {
                        $afterNow = $c;
                    }
                    $c++;
                }
                $afterNow = $afterNow * (-1);
            }
        }
        /**
         *
         * @var Event $e
         */
        foreach ($events as $e) {
            $e->setCurrent($afterNow++);
            // debug($e);
        }
        return $events;
    }

    /**
     *
     * @param string $table
     * @return QueryBuilder
     */
    private function getQueryBuilder(string $table): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }

    /**
     *
     * @param string $id
     * @throws \Exception
     * @return array
     */
    private function getStorageUid(string $id): array
    {
        $qb = $this->getQueryBuilder('tt_content');
        $s = $qb->select('list_type', 'pages', 'pi_flexform')
            ->from('tt_content')
            ->where($qb->expr()
                ->eq('uid', $qb->createNamedParameter($id)))
            ->execute();
        if ($row = $s->fetchAssociative()) {
            $contentElement = $row;
        } else {
            throw new \Exception("Content element $id found.");
        }
        if ($row = $s->fetchAssociative()) {
            throw new \Exception("Two content elements with $id found? Database corrupt?");
        }
        if ($contentElement['list_type'] == DutyRosterController::LIST_TYPE) {
            return GeneralUtility::intExplode(',', $contentElement['pages'], TRUE);
        }
        return [];
    }

    /**
     *
     * @return void
     */
    public function showAction(): void
    {
        $events = array();
        switch ($this->settings['renderType']) {
            case 'little':
                $events = $this->eventRepository->findPublicEvents(intval($this->settings['smallEventCount']), null, true, true);
                break;
            case 'big':
            default:
                $events = $this->eventRepository->findPublicEvents();
        }
        $this->view->assign('settings', $this->settings);
        $this->view->assign('events', $this->prepareEvents($events));
        $this->view->assign('uid', $this->configurationManager->getContentObject()->data['uid']);
    }

    /**
     *
     * @param string $id
     * @return void
     */
    public function downloadIcsAction(string $id)
    {
        $normalizedParams = $this->request->getAttribute('normalizedParams');
        $baseUri = $normalizedParams->getSiteUrl();

        $this->view->assign('domain', parse_url($baseUri, PHP_URL_HOST));
        $this->view->assign('events', $this->prepareEvents($this->eventRepository->findPublicEvents(EventRepository::UNLIMITED, $this->getStorageUid($id), false)));
    }



    /**
     * @param string $id
     */
    public function reasonsForPreventionAction(string $id): string
    {

        $parsedBody = $this->request->getParsedBody();

        if (is_array($parsedBody)) {


            $from = $this->getValidDate($parsedBody['from']);
            $until = $this->getValidDate($parsedBody['until']);
            $visibility = $this->getEventVisiblity($parsedBody);

            if ($from != null && $until != null) {
                return json_encode($this->eventRepository->findEventsAt($this->getStorageUid($id), $from, $until + (24 * 3600), $visibility));
            }
        }

        $tmp = date_parse_from_format('Y-m-d', $parsedBody['from']);
        return json_encode([
            'parsedBody' => $parsedBody,

            'isArray' => is_array($parsedBody),

            'from' => $from,
            'preg' => preg_match(DutyRosterController::DATE_PATTERN, $parsedBody['from']) > 0,
            'fd' => $tmp,
            'e' => isset($tmp['errors']),
            'ec' => count($tmp['errors'])
        ]);

    }

    private function getEventVisiblity($parsedBody): int
    {
        if (!isset($parsedBody['visibility'])) {
            return PublicOption::PUBLIC;
        } else if (strtoupper($parsedBody['visibility']) === 'ALL') {
            return PublicOption::ALL;
        } else if (strtoupper($parsedBody['visibility']) === 'INTERNAL') {
            return PublicOption::INTERNAL;
        } else {
            return PublicOption::PUBLIC;
        }
    }


    private function getValidDate(string $rawDate): string
    {
        if (preg_match(DutyRosterController::DATE_PATTERN, $rawDate)) {
            $date = date_parse_from_format('Y-m-d', $rawDate);
            if (!isset($date['errors']) || count($date['errors']) == 0) {
                $dt = new \DateTime();
                $dt->setTimezone(new \DateTimeZone('Europe/Berlin'));
                $dt->setTime(0, 0, 0, 0);
                $dt->setDate($date['year'], $date['month'], $date['day']);
                return $dt->getTimestamp();
            }
        }
        return null;
    }


}