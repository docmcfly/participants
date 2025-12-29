<?php
namespace Cylancer\Participants\Controller;

use Cylancer\Participants\Service\MiscService;
use Cylancer\Participants\Service\PersonalDutyRosterService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Cylancer\Participants\Domain\Model\PersonalDutyRosterGroupFilterSettings;
use Cylancer\Participants\Domain\Model\PersonalDutyRosterGroupFilterSet;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use Cylancer\Participants\Domain\Repository\CommitmentRepository;
use Cylancer\Participants\Service\FrontendUserService;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use Cylancer\Participants\Domain\Model\FrontendUser;
use Cylancer\Participants\Domain\Repository\FrontendUserRepository;

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
class PersonalDutyRosterController extends AbstractController
{

    // SETTINGS -------------------------------------------
    private const CAN_VIEW_MEMBERS = 'canViewMembers';

    private const CAN_VIEW_CURRENTLY_OFF_DUTY = 'canViewCurrentlyOffDuty';

    public const PERSONAL_DUTY_ROSTER_GROUPS = 'personalDutyRosterGroups';

    public const OPTIONAL_HIDDEN_PERSONAL_DUTY_ROSTER_GROUPS = 'optionalHiddenPersonalDutyRosterGroups';

    public const DUTY_ROSTER_STORAGE_UIDS = 'dutyRosterStorageUids';

    public const PLANNING_STORAGE_UID = 'planningStorageUid';

    // ----------------------------------------------------
    private const LIST_TYPE = 'participants_personaldutyroster';

    public function __construct(
        private MiscService $miscService,
        private readonly FrontendUserService $frontendUserService,
        private SiteFinder $siteFinder,
        private Context $context,
        private readonly CommitmentRepository $commitmentRepository,
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly PersonalDutyRosterService $personalDutyRosterService,
    ) {
        parent::__construct(
            $miscService,
            $frontendUserService,
            $siteFinder,
            $context
        );
    }

    public function initializeSetPersonalDutyRosterFilterAction(): void
    {
        if ($this->arguments->hasArgument('personalDutyRosterFilterSettings')) {
            /** @var MvcPropertyMappingConfiguration $pmc */
            $pmc = $this->arguments->getArgument('personalDutyRosterFilterSettings')->getPropertyMappingConfiguration();
            $pmc->setTargetTypeForSubProperty('settings', 'array');
        }
    }

    public function setPersonalDutyRosterFilterAction(PersonalDutyRosterGroupFilterSettings $personalDutyRosterFilterSettings): ResponseInterface
    {
        if ($this->frontendUserService->isLogged()) {
            /**
             *
             * @var FrontendUser $u
             * @var PersonalDutyRosterGroupFilterSet $entry
             */
            // remove old settings
            $u = $this->frontendUserService->getCurrentUser();
            foreach ($u->getHiddenPersonalDutyRosterGroups()->toArray() as $ug) {
                $u->removeHiddenPersonalDutyRosterGroups($ug);
            }

            // add new settings (hidden groups)
            foreach ($personalDutyRosterFilterSettings->getSettings() as $entry) {
                if (!$entry->getVisible()) {
                    $u->addHiddenPersonalDutyRosterGroups($entry->getFrontendUserGroup());
                }
            }
            $u->setOnlyScheduledEvents($personalDutyRosterFilterSettings->getOnlyScheduledEvents());
            // debug($u);
            $this->frontendUserRepository->update($u);
        }
        return GeneralUtility::makeInstance(ForwardResponse::class, 'show');
    }

    public function showAction(): ResponseInterface
    {
        if ($this->frontendUserService->isLogged()) {
            /**
             *
             * @var PersonalDutyRosterGroupFilterSettings $personalDutyRosterFilterSettings
             * @var array $personalDutyRosterGroups
             */
            [$personalDutyRosterGroups, $personalDutyRosterFilterSettings] = $this->personalDutyRosterService->getPersonalDutyRosterFilterSettings($this->settings);
            $dutyRosterStorageUids = GeneralUtility::intExplode(',', $this->settings[PersonalDutyRosterController::DUTY_ROSTER_STORAGE_UIDS], true);
            $planningStorageUid = \intval($this->settings[PersonalDutyRosterController::PLANNING_STORAGE_UID]);

            /** @var FrontendUser $user       */
            $user = $this->frontendUserService->getCurrentUser();

            $personalDutyRosterFilterSettings->setOnlyScheduledEvents($user->getOnlyScheduledEvents());

            $canViewMembers = false;
            $allowGroup = $this->settings['canViewMembers'];
            foreach ($user->getUserGroup() as $ug) {
                if ($this->frontendUserService->contains($ug, $allowGroup)) {
                    $canViewMembers = true;
                    break;
                }
            }
            $this->view->assign('canViewMembers', $canViewMembers);

            $canViewCurrentlyOffDuty = false;
            $allowGroup = $this->settings['canViewCurrentlyOffDuty'];
            foreach ($user->getUserGroup() as $ug) {
                if ($this->frontendUserService->contains($ug, $allowGroup)) {
                    $canViewCurrentlyOffDuty = true;
                    break;
                }
            }
            $this->view->assign('canViewCurrentlyOffDuty', $canViewCurrentlyOffDuty);

            $startMoment = new \DateTime();
            if ($canViewMembers) {
                $startMoment->add(\DateInterval::createFromDateString('yesterday'));
            }

            $commitments = $this->commitmentRepository->findCurrentEventCommitments($user, $dutyRosterStorageUids, $planningStorageUid, $personalDutyRosterGroups, $personalDutyRosterFilterSettings, $startMoment);
            $this->view->assign('commitments', $commitments);
            $this->view->assign('counts', $this->commitmentRepository->getEventCommitmentCounts($planningStorageUid));
            $this->view->assign('personalDutyRosterFilterSettings', $personalDutyRosterFilterSettings);

            $this->view->assign('displayScheduledStateIfExternalPlanningUse', $this->settings['displayScheduledStateIfExternalPlanningUse'] ?? true);
            $this->view->assign('ceUid', $this->getContentObjectUid());
            // debug($this->view);
        } else {
            $this->view->assign('counts', []);
        }
        return $this->htmlResponse();
    }

    private function getContentObjectUid(): int
    {
        return $this->request->getAttribute('currentContentObject')->data['uid'];
    }


}