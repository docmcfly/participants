<?php
namespace Cylancer\Participants\Controller;

use Cylancer\Participants\Domain\Model\Event;
use Cylancer\Participants\Service\EventService;
use Cylancer\Participants\Service\FrontendUserService;
use Cylancer\Participants\Service\ICalService;
use Cylancer\Participants\Service\MiscService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\SiteFinder;
use Cylancer\Participants\Domain\Repository\EventRepository;

/**
 *
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */
class DutyRosterController extends AbstractController
{

    private const CONTENT_ELEMENT = 'contentElement';

    private const LIST_TYPE = 'participants_dutyroster';

    private const DATE_PATTERN = '/^\d{4}-(((0[13578]|1[02])-(([012]\d)|3[01]))|((0[469]|11)-(([012]\d)|30))|02-[012]\d)$/m';


    public function __construct(
        private MiscService $miscService,
        private FrontendUserService $frontendUserService,
        private SiteFinder $siteFinder,
        private Context $context,
        private readonly EventRepository $eventRepository,
        private readonly EventService $eventService,
        private readonly ICalService $icalService,
    ) {
        parent::__construct(
            $miscService,
            $frontendUserService,
            $siteFinder,
            $context
        );
    }

    private function prepareEvents(array $events): array
    {
        $now = time();
        $c = \count($events);

        if ($c == 0) {
            $afterNow = -1;
        } else {
            $lastEvent = $events[\count($events) - 1];
            if ($lastEvent->getDateTime()->getTimestamp() < $now) {
                $afterNow = \count($events) * (-1);
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

    private function getContentElementUid(): int
    {
        return $this->request->getAttribute('currentContentObject')->data['uid'];
    }


    public function showAction(): ResponseInterface
    {

        $ceUid = $this->getContentElementUid();
        $flexformSettings = $this->getFlexformSettings($ceUid);

        $this->view->assign('settings', $this->settings);
        $events = [];
        switch ($this->settings['renderType'] ?? 'big') {
            case 'little':
                $events = $this->eventRepository->findEvents(
                    limit: intval(value: $this->settings['smallEventCount'] ?? '5'),
                    storageUids: $flexformSettings['_pages'],
                    inclusiveCanceledEvents: true,
                    startWithToday: true,
                    onlyPublic: !$this->frontendUserService->isLogged()
                );

                $this->view->assign('events', $this->prepareEvents($events));
                $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);
                return $this->htmlResponse();

            case 'big':
                $events = $this->eventRepository->findEvents(
                    storageUids: $flexformSettings['_pages'],
                    inclusiveCanceledEvents: true,
                    startWithToday: true,
                    onlyPublic: !$this->frontendUserService->isLogged(),
                );
                $this->view->assign('events', $this->prepareEvents($events));
                $this->view->assign('uid', $this->request->getAttribute('currentContentObject')->data['uid']);
                return $this->htmlResponse();
            case 'calendar':
                $this->view->assign('ceUid', $ceUid);
                $this->view->assign('appointmentSymbol', $flexformSettings['calendarAppointmentSymbol'] ?? ' 🕗');

                $mode = $this->settings['calendarInitialMode'] ?? 'startTodayMonthRelative';
                if (str_starts_with($mode, 'startFixDate')) {
                    $currentDay = $this->settings['calendarFixStartDate'] ?? date('Y-m-d');
                    if ($currentDay === '0') {
                        $currentDay = date('Y-m-d');
                    }
                    $this->view->assign('monthSelectorsReferenceFunction', 'function (c) {  return c.properties.currentDay  }');
                } else {
                    $currentDay = date('Y-m-d');
                    $this->view->assign('monthSelectorsReferenceFunction', 'function(calendar) { calendar.today }');
                }
                $this->view->assign('currentDay', $currentDay);

                $currentYear = substr($currentDay, 0, 4);
                $currentMonth = substr($currentDay, 5, 2);

                $calendarMaxMonthsBack = intval($this->settings['calendarMaxMonthsBack'] ?? 1);
                if (str_ends_with($mode, 'CurrentYear')) {
                    $this->view->assign('calendarMaxPastMonth', $currentMonth - 1);
                } else {
                    $this->view->assign('calendarMaxPastMonth', $calendarMaxMonthsBack);
                }
                $calendarMaxMonthsForward = intval($this->settings['calendarMaxMonthsForward'] ?? 12);
                if (str_ends_with($mode, 'CurrentYear')) {
                    $this->view->assign('calendarMaxFutureMonth', 12 - $currentMonth);
                } else {
                    $this->view->assign('calendarMaxFutureMonth', $calendarMaxMonthsForward);
                }

                $this->view->assign('language', $this->getLanguage());
                $this->view->assign(
                    'currentMonthEvents',
                    json_encode($this->eventService->getCalendarEvents(
                        $currentYear,
                        $currentMonth,
                        $ceUid,
                        !$this->frontendUserService->isLogged()
                    ))
                );
                return $this->htmlResponse();
            default:
                throw new \Exception('Unkonw render type: ' . $this->settings['renderType']);
        }
    }

    public function downloadIcsAction(string $id): ResponseInterface
    {
        $normalizedParams = $this->request->getAttribute('normalizedParams');
        $baseUri = $normalizedParams->getSiteUrl();

        $ical = $this->icalService->createICal(
            parse_url($baseUri, PHP_URL_HOST),
            $this->eventRepository->findEvents(
                EventRepository::UNLIMITED,
                $this->getFlexformSettings($id)['_pages'],
                false,
                false,
                false

            )
        );

        $response = $this->responseFactory->createResponse();
        $response = $response->withAddedHeader('Content-Type', 'text/text;charset=utf-8');
        $response = $response->withAddedHeader('Content-Disposition', 'inline; filename="export.txt"');
        $response->getBody()->write($ical);

        return $response;
    }

}