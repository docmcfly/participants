<?php
namespace Cylancer\Participants\Controller;

use Cylancer\Participants\Domain\Model\Commitment;
use Cylancer\Participants\Domain\Model\Event;
use Cylancer\Participants\Domain\Repository\CommitmentRepository;
use Cylancer\Participants\Domain\Repository\EventRepository;
use Cylancer\Participants\Service\FrontendUserService;
use Cylancer\Participants\Service\ICalService;
use Cylancer\Participants\Service\MiscService;
use Cylancer\Participants\Service\PersonalDutyRosterService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Site\SiteFinder;
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
class ICalController extends AbstractController
{


    public function __construct(
        private MiscService $miscService,
        private readonly FrontendUserService $frontendUserService,
        private SiteFinder $siteFinder,
        private Context $context,
        private readonly ICalService $icalService,
        private readonly EventRepository $eventRepository,
        private readonly CommitmentRepository $commitmentRepository,
        private readonly PersonalDutyRosterService $personalDutyRosterService,
    ) {
        parent::__construct(
            $miscService,
            $frontendUserService,
            $siteFinder,
            $context
        );
    }

    public function downloadICalAction(string $id): ResponseInterface
    {
        $ical = $this->icalService->createICal(
            $this->getDomain(),
            $this->eventRepository->findEvents(
                limit: EventRepository::UNLIMITED,
                storageUids: $this->getFlexformSettings($id)['_pages'],
                inclusiveCanceledEvents: false,
                startWithToday: $this->frontendUserService->isLogged()
            )
        );
        throw new PropagateResponseException($this->createIcalResponse($ical), 200);
    }

    private function getDomain(): string
    {
        $normalizedParams = $this->request->getAttribute('normalizedParams');
        $baseUri = $normalizedParams->getSiteUrl();
        return parse_url($baseUri, PHP_URL_HOST);
    }

    public function downloadAllVisibleCalendarEntriesAction(int $id): ResponseInterface
    {
        throw new PropagateResponseException(
            $this->createIcalResponse(
                $this->toICal(
                    $this->getCurrentUserCommitments($id)
                )
            ),
            200
        );
    }

    public function downloadCalendarEntryAction(int $id, int $commitmentUid): ResponseInterface
    {

        if ($this->frontendUserService->isLogged()) {
            $planningStorageUid = $this->settings[PersonalDutyRosterController::PLANNING_STORAGE_UID];
            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings */
            $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
            $querySettings->setStoragePageIds([$planningStorageUid]);
            $this->commitmentRepository->setDefaultQuerySettings($querySettings);

            /** @var Commitment $commitment */
            $commitment = $this->commitmentRepository->findByUid($commitmentUid);

            throw new PropagateResponseException($this->createIcalResponse(
                $this->icalService->createICal($this->getDomain(), [$commitment->getEvent()])
            ), 200);

        } else {
            throw new \Exception('You ar not logged in');
        }
    }

    /**
     * @param Commitment[] $commitments
     * @return Event[]
     */
    private function getPresentEvents(array $commitments): array
    {
        $events = [];
        foreach ($commitments as $c) {
            if ($c->isPresent()) {
                $events[] = $c->getEvent();
            }
        }
        return $events;
    }

    public function downloadAllPromisedVisibleCalendarEntriesAction(int $id): ResponseInterface
    {
        throw new PropagateResponseException($this->createIcalResponse(
            $this->icalService->createICal(
                $this->getDomain(),
                $this->getPresentEvents($this->getCurrentUserCommitments($id))
            )
        ), 200);
    }

    public function downloadAllPromisedCalendarEntriesAction(int $id): ResponseInterface
    {
        throw new PropagateResponseException($this->createIcalResponse(
            $this->icalService->createICal(
                $this->getDomain(),
                $this->getPresentEvents($this->getCurrentUserCommitments($id, true))
            )
        ), 200);
    }

    private function createIcalResponse(string $content, string $fileName = 'export.ics'): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $response = $response->withAddedHeader('Content-Type', 'text/Calendar;charset=utf-8');
        $response = $response->withAddedHeader('Content-Disposition', 'inline; filename="' . $fileName . '"');
        $response->getBody()->write($content);
        return $response;
    }


    /**
     * Undocumented function
     *
     * @param Commitment[] $commitmentens
     * @return string
     */
    private function toICal(array $commitmentens): string
    {

        $events = [];
        foreach ($commitmentens as $commitment) {
            $events[] = $commitment->getEvent();
        }

        return $this->icalService->createICal($this->getDomain(), $events);
    }

    private function getCurrentUserCommitments(int $id, bool $ignoreCurrentUserSettings = false): array
    {
        if ($this->frontendUserService->isLogged()) {
            $flexSettings = $this->getFlexformSettings($id);

            $personalDutyRosterGroups = $flexSettings[PersonalDutyRosterController::PERSONAL_DUTY_ROSTER_GROUPS];
            $planningStorageUid = $flexSettings[PersonalDutyRosterController::PLANNING_STORAGE_UID];
            $dutyRosterStorageUids = GeneralUtility::intExplode(',', $flexSettings[PersonalDutyRosterController::DUTY_ROSTER_STORAGE_UIDS], true);

            list($personalDutyRosterGroups, $personalDutyRosterFilterSettings) = $this->personalDutyRosterService->getPersonalDutyRosterFilterSettings($flexSettings, $ignoreCurrentUserSettings);
            return $this->commitmentRepository->findCurrentEventCommitments(
                $this->frontendUserService->getCurrentUser(),
                $dutyRosterStorageUids,
                $planningStorageUid,
                $personalDutyRosterGroups,
                $personalDutyRosterFilterSettings,
                new \DateTime(),
                false
            );

        } else {
            throw new \Exception('You ar not logged in');
        }
    }

}