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
     * @var EventRepository
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
            if ($lastEvent->getDateTime()->getTimestamp() < $now) {
                $afterNow = count($events) * (-1);
            } else {
                $afterNow = -1;
                $c = 0;

                /**
                 *
                 * @var Event $e
                 */
                foreach ($events as $e) {
                    if ($afterNow == -1 && $e->getDateTime()->getTimestamp() > $now) {
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





}