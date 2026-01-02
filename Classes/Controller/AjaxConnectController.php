<?php
namespace Cylancer\Participants\Controller;

use Cylancer\Participants\Domain\Model\Commitment;
use Cylancer\Participants\Domain\PresentState;
use Cylancer\Participants\Domain\Repository\CommitmentRepository;
use Cylancer\Participants\Service\EventService;
use Cylancer\Participants\Service\FrontendUserService;
use Cylancer\Participants\Service\MiscService;
use Cylancer\Participants\Utility\Utility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
class AjaxConnectController extends AbstractController
{
    public function __construct(
        private MiscService $miscService,
        private FrontendUserService $frontendUserService,
        private SiteFinder $siteFinder,
        private Context $context,
        private readonly EventService $eventService,
        private readonly CommitmentRepository $commitmentRepository,
        private readonly PersistenceManager $persistenceManager,
    ) {
        parent::__construct(
            $miscService,
            $frontendUserService,
            $siteFinder,
            $context
        );
    }

    public function getEventsAction(int $ceUid): ResponseInterface
    {
        $parsedBody = $this->request->getParsedBody();
        if (\is_array($parsedBody)) {
            $year = \intval($parsedBody['year']);
            $month = \intval($parsedBody['month']);
            throw new PropagateResponseException(
                $this->jsonResponse(
                    json_encode(
                        $this->eventService->getCalendarEvents($year, $month, $ceUid, !$this->frontendUserService->isLogged())
                    )
                ),
                200
            );
        }
        return throw new PropagateResponseException($this->jsonResponse(json_encode([])), 500);
    }

    public function setPresentAction(Commitment $commitment, int $ceUid): ResponseInterface
    {
        $return = [];
        if ($commitment->getUser()->getUid() == $this->frontendUserService->getCurrentUserUid()) {
            $eventUid = $commitment->getEvent()->getUid();
            $return['eventUid'] = $eventUid;
            $return['present'] = $commitment->getPresent();
            if (!$commitment->_isNew() && $commitment->_isDirty('present')) {
                $settings = $this->getFlexformSettings($ceUid);
                $this->commitmentRepository->update($commitment);
                $this->persistenceManager->persistAll();
            } else {
                $settings = $this->settings;
            }
            $cc = $this->commitmentRepository->getEventCommitmentCounts(\intval($settings[PersonalDutyRosterController::PLANNING_STORAGE_UID]), $eventUid);
            $return['counts'] = Utility::calculatePresentDatas($cc['presentCount'], $cc['presentDefaultCount']);
        } else {
            $return['present'] = false;
            $return['counts'] = [
                'presentCount' => 0,
                'presentDefaultCount' => 0,
                'presentOverCount' => 0,
                'presentPercent' => 0,
                'presentOverPercent' => 0,
                'displayPercent' => 0
            ];
        }

        throw new PropagateResponseException(
            $this->jsonResponse(json_encode($return)),
            200
        );
    }

    public function getMembersAction(Commitment $commitment, int $ceUid): ResponseInterface
    {
        $return = [];
        if ($commitment->getUser()->getUid() == $this->frontendUserService->getCurrentUserUid()) {
            $eventUid = $commitment->getEvent()->getUid();
            $settings = $this->getFlexformSettings($ceUid);
            $planningStorageUid = intval($settings[PersonalDutyRosterController::PLANNING_STORAGE_UID]);
            $return['members'] = $this->commitmentRepository->getEventCommitments(PresentState::PRESENT, $planningStorageUid, $eventUid, null);
            $return['dropouts'] = $this->commitmentRepository->getEventCommitments(PresentState::NOT_PRESENT, $planningStorageUid, $eventUid);
            $return['undecideds'] = $this->commitmentRepository->getEventCommitments(PresentState::UNKNOWN, $planningStorageUid, $eventUid);
        } else {
            $return['members'] = [];
        }
        throw new PropagateResponseException(
            $this->jsonResponse(json_encode($return)),
            200
        );
    }

}