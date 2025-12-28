<?php
namespace Cylancer\Participants\Service;

use Cylancer\Participants\Domain\Model\Event;
use Cylancer\Participants\Domain\PublicOption;
use Cylancer\Participants\Domain\Repository\EventRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 *
 * This file is part of the "participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */

class EventService implements SingletonInterface
{


    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly MiscService $miscService
    ) {

    }


    /**
     * @param int $year
     * @param int $month
     * @param int[] $storagePids
     * @param bool $isPublic
     * @return array
     */
    public function getCalendarEvents(int $year, int $month, int $ceUid = null, bool $isPublic = false): array
    {
        $events = [];
        if ($ceUid != null) {
            $flexformSettings = $this->miscService->getFlexformSettings($ceUid);
            $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
            $querySettings->setStoragePageIds($flexformSettings['_pages']);
            $this->eventRepository->setDefaultQuerySettings($querySettings);

            /** @var \DateTime $startDate */
            $startDate = MiscService::toDateTime($year, $month);
            $startDate = MiscService::addDays($startDate, 1 - intval($startDate->format("N")));

            /** @var \DateTime $endDate */
            $endDate = MiscService::toDateTime($year, $month);
            $endDate = MiscService::addMonths($endDate, 1);
            $endDate = MiscService::addDays($endDate, 7 - intval($endDate->format("N")));



            foreach ($this->eventRepository->findEventsAt($startDate, $endDate, $isPublic ? PublicOption::PUBLIC : PublicOption::ALL, EventRepository::UNLIMITED, true) as $event) {
                /** @var Event $event*/
                $tmp = [];
                $tmp['idx'] = $event->getUid();
                if ($event->getFullDay()) {
                    $tmp['start'] = $event->getDateString() . ' 00:00:00';
                    $tmp['end'] = $event->getDateString();
                } else if ($event->getOpenEnd()) {
                    $tmp['start'] = $event->getDateString() . ' ' . $event->getTimeString();
                    $tmp['end'] = $event->getDateString();
                } else {
                    $start = $event->getDateString() . ' ' . $event->getTimeString();
                    $tmp['start'] = $start;
                    $tmp['end'] = MiscService::addHours(MiscService::stringDateTimetoDateTime($start), $event->getDuration())->format('Y-m-d H:i:s');

                }
                $tmp['title'] = $event->getEventType()->getTitle();

                $tmp['description'] = $isPublic ? $event->getPublicVisibleDescription() : $event->getDescription();
                $ugs = [];

                foreach ($event->getPublicUsergroups() as $ug) {
                    $ugs[] = $ug->getTitle();
                }

                $tmp['responsible'] = implode(',', $ugs);
                $bgColor = $event->getEventType()->getColor();
                if ($flexformSettings['calendarUseEventTypeColor'] ?? false && $bgColor) {
                    $tmp['backgroundColor'] = $bgColor;
                } else {
                    $tmp['cssClass'] = 'dutyRosterCalendarEvent';
                }
                $tmp['striped'] = false;

                $events[] = $tmp;
            }
        }
        return $events;
    }
}