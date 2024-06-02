<?php
namespace Cylancer\Participants\Controller;

use Cylancer\Participants\Domain\PresentState;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Cylancer\Participants\Utility\Utility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use Cylancer\Participants\Domain\Model\PersonalDutyRosterGroupFilterSettings;
use Cylancer\Participants\Domain\Model\PersonalDutyRosterGroupFilterSet;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Cylancer\Participants\Domain\Repository\CommitmentRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Cylancer\Participants\Domain\Repository\FrontendUserGroupRepository;
use Cylancer\Participants\Service\FrontendUserService;
use Cylancer\Participants\Domain\Model\FrontendUserGroup;
use Cylancer\Participants\Domain\Model\Commitment;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use Cylancer\Participants\Domain\Model\FrontendUser;
use Cylancer\Participants\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 *
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 C.Gogolin <service@cylancer.net>
 *
 * @package Cylancer\Participants\Controller
 */
class PersonalDutyRosterController extends ActionController
{

    // SETTINGS -------------------------------------------
    const CAN_VIEW_MEMBERS = 'canViewMembers';

    const CAN_VIEW_CURRENTLY_OFF_DUTY = 'canViewCurrentlyOffDuty';

    const PERSONAL_DUTY_ROSTER_GROUPS = 'personalDutyRosterGroups';

    const OPTIONAL_HIDDEN_PERSONAL_DUTY_ROSTER_GROUPS = 'optionalHiddenPersonalDutyRosterGroups';

    const DUTY_ROSTER_STORAGE_UIDS = 'dutyRosterStorageUids';

    const PLANNING_STORAGE_UID = 'planningStorageUid';

    // ----------------------------------------------------
    const LIST_TYPE = 'participants_personaldutyroster';

    /** @var FrontendUserService */
    private $frontendUserService = null;

    /** @var CommitmentRepository */
    private $commitmentRepository = null;

    /**  @var FrontendUserRepository */
    private $frontendUserRepository = null;

    /** @var FrontendUserGroupRepository */
    private $frontendUserGroupRepository = null;

    /** @var PersistenceManager */
    private $persistenceManager = null;

    public function __construct(
        FrontendUserService $frontendUserService,
        CommitmentRepository $commitmentRepository,
        FrontendUserRepository $frontendUserRepository,
        //
        FrontendUserGroupRepository $frontendUserGroupRepository,
        PersistenceManager $persistenceManager
    ) {
        $this->frontendUserService = $frontendUserService;
        $this->commitmentRepository = $commitmentRepository;
        $this->frontendUserRepository = $frontendUserRepository;
        $this->frontendUserGroupRepository = $frontendUserGroupRepository;
        $this->persistenceManager = $persistenceManager;
    }

    /**
     *
     * @return void
     */
    public function initializeSetPersonalDutyRosterFilterAction(): void
    {
        if ($this->arguments->hasArgument('personalDutyRosterFilterSettings')) {
            /** @var MvcPropertyMappingConfiguration $pmc */
            $pmc = $this->arguments->getArgument('personalDutyRosterFilterSettings')->getPropertyMappingConfiguration();
            $pmc->setTargetTypeForSubProperty('settings', 'array');
        }
    }

    /**
     *
     * @param PersonalDutyRosterGroupFilterSettings $personalDutyRosterFilterSettings
     * @return ResponseInterface
     */
    public function setPersonalDutyRosterFilterAction(PersonalDutyRosterGroupFilterSettings $personalDutyRosterFilterSettings): ResponseInterface
    {
        if ($this->frontendUserService->isLogged()) {
            /**
             *
             * @var FrontendUser $u
             * @var PersonalDutyRosterGroupFilterSet $entry
             */
            // remove old settings
            $u = $this->frontendUserRepository->findByUid($this->frontendUserService->getCurrentUserUid());
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

    /**
     * action set defaults
     *
     * @return ResponseInterface
     */
    public function showAction(): ResponseInterface
    {
        if ($this->frontendUserService->isLogged()) {
            $this->prepareSettings($this->settings);
            // debug($this->settings);
            /**
             *
             * @var PersonalDutyRosterGroupFilterSettings $personalDutyRosterFilterSettings
             * @var array $personalDutyRosterGroups
             */
            list($personalDutyRosterGroups, $personalDutyRosterFilterSettings) = $this->getPersonalDutyRosterFilterSettings();
            // debug($personalDutyRosterGroups);
            // debug($personalDutyRosterFilterSettings);

            $dutyRosterStorageUids = $this->settings[PersonalDutyRosterController::DUTY_ROSTER_STORAGE_UIDS];
            $planningStorageUid = $this->settings[PersonalDutyRosterController::PLANNING_STORAGE_UID];

            /** @var FrontendUser $user       */
            $user = $this->frontendUserRepository->findByUid($this->frontendUserService->getCurrentUserUid());

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
            $this->view->assign('uid', $this->getContentObjectUid());
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


    private function getUrl(): string
    {
        return GeneralUtility::makeInstance(SiteFinder::class)
            ->getSiteByPageId($this->getContentObjectUid())
            ->getBase()
            ->__toString();
    }


    /**
     * The view contains a iCal list.
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function downloadAllVisibleCalendarEntriesAction(int $id): ResponseInterface
    {
        $this->view->assign('commitments', $this->getCurrentUserCommitments($id));
        $this->view->assign('domain', $this->getUrl());
        return $this->htmlResponse();
    }

    /**
     * The view contains a iCal list.
     *
     * @param int $id
     * @param int $commitmentUid
     * @return ResponseInterface
     */
    public function downloadCalendarEntryAction(int $id, int $commitmentUid): ResponseInterface
    {

        if ($this->frontendUserService->isLogged()) {
            $this->settings = $this->getPreparedSettings($id);

            $planningStorageUid = $this->settings[PersonalDutyRosterController::PLANNING_STORAGE_UID];
            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings */
            $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
            $querySettings->setStoragePageIds([$planningStorageUid]);
            $this->commitmentRepository->setDefaultQuerySettings($querySettings);

            $this->commitmentRepository->findByUid($commitmentUid);

            $this->view->assign('commitments', [$this->commitmentRepository->findByUid($commitmentUid)]);
            $this->view->assign('domain', $this->getUrl());
        } else {
            throw new \Exception('You ar not logged in');
        }
        return $this->htmlResponse();
    }




    /**
     * The view contains a iCal list.
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function downloadAllPromisedVisibleCalendarEntriesAction(int $id): ResponseInterface
    {
        $commitments = array();
        $tmp = $this->getCurrentUserCommitments($id);

        // debug($tmp);:void
        foreach ($tmp as $c) {
            if ($c->isPresent()) {
                $commitments[] = $c;
            }
        }

        $this->view->assign('commitments', $commitments);
        $this->view->assign('domain', $this->getUrl());

        return $this->htmlResponse();
    }

    /**
     * The view contains a iCal list.
     *
     * @param int $id
     * @return ResponseInterface
     */
    public function downloadAllPromisedCalendarEntriesAction(int $id): ResponseInterface
    {
        $commitments = array();
        $tmp = $this->getCurrentUserCommitments($id, true);
        foreach ($tmp as $c) {
            if ($c->isPresent()) {
                $commitments[] = $c;
            }
        }
        $this->view->assign('commitments', $commitments);
        $this->view->assign('domain', $this->getUrl());
        return $this->htmlResponse();
    }

    /**
     * action sets the present value
     *
     * @param Commitment $commitment
     * @return ResponseInterface
     */
    public function setPresentAction(Commitment $commitment): ResponseInterface
    {
        $return = array();
        if ($commitment->getUser()->getUid() == $this->frontendUserService->getCurrentUserUid() && $this->request->hasArgument('id')) {
            $eventUid = $commitment->getEvent()->getUid();
            $return['eventUid'] = $eventUid;
            $return['present'] = $commitment->getPresent();
            if (!$commitment->_isNew() && $commitment->_isDirty('present')) {
                $settings = $this->getPreparedSettings(intval($this->request->getArgument('id')));
                $this->commitmentRepository->update($commitment);
                $this->persistenceManager->persistAll();
            }
            $cc = $this->commitmentRepository->getEventCommitmentCounts($settings[PersonalDutyRosterController::PLANNING_STORAGE_UID], $eventUid);
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
        return $this->jsonResponse(json_encode($return));
    }

    /**
     *
     * @param Commitment $commitment
     * @param string $id
     * @return ResponseInterface
     */
    public function getMembersAction(Commitment $commitment): ResponseInterface
    {
        $return = array();

        $return['args'] = $this->request->getArguments();
        $return['c_user'] = $commitment->getUser()->getUid();
        $return['fe_user'] = $this->frontendUserService->getCurrentUserUid();
        $return['id'] = $this->request->getArgument('id');

        if ($commitment->getUser()->getUid() == $this->frontendUserService->getCurrentUserUid()) {
            $eventUid = $commitment->getEvent()->getUid();
            $settings = $this->request->hasArgument('id') ? $this->getPreparedSettings(intval($this->request->getArgument('id'))) : $this->getPreparedSettings();
            $return['event_uid'] = $eventUid;
            $return['members'] = $this->commitmentRepository->getEventCommitments(PresentState::PRESENT, $settings[PersonalDutyRosterController::PLANNING_STORAGE_UID], $eventUid, null);
            $return['dropouts'] = $this->commitmentRepository->getEventCommitments(PresentState::NOT_PRESENT, $settings[PersonalDutyRosterController::PLANNING_STORAGE_UID], $eventUid);
            $return['undecideds'] = $this->commitmentRepository->getEventCommitments(PresentState::UNKNOWN, $settings[PersonalDutyRosterController::PLANNING_STORAGE_UID], $eventUid);
            return $this->jsonResponse(json_encode($return));
        } else {
            $return['members'] = [];
        }
        return $this->jsonResponse(json_encode($return));
    }

    /**
     *
     * @param array $settings
     * @return array
     */
    private function prepareSettings(array &$settings): array
    {
        $settings[PersonalDutyRosterController::CAN_VIEW_MEMBERS] = intval($settings[PersonalDutyRosterController::CAN_VIEW_MEMBERS]);
        $settings[PersonalDutyRosterController::CAN_VIEW_CURRENTLY_OFF_DUTY] = intval($settings[PersonalDutyRosterController::CAN_VIEW_CURRENTLY_OFF_DUTY]);
        $settings[PersonalDutyRosterController::PERSONAL_DUTY_ROSTER_GROUPS] = $this->toIntArray($settings[PersonalDutyRosterController::PERSONAL_DUTY_ROSTER_GROUPS]);
        $settings[PersonalDutyRosterController::OPTIONAL_HIDDEN_PERSONAL_DUTY_ROSTER_GROUPS] = $this->toIntArray($settings[PersonalDutyRosterController::OPTIONAL_HIDDEN_PERSONAL_DUTY_ROSTER_GROUPS]);
        $settings[PersonalDutyRosterController::DUTY_ROSTER_STORAGE_UIDS] = $this->toIntArray($settings[PersonalDutyRosterController::DUTY_ROSTER_STORAGE_UIDS]);
        $settings[PersonalDutyRosterController::PLANNING_STORAGE_UID] = intval($settings[PersonalDutyRosterController::PLANNING_STORAGE_UID]);
        return $settings;
    }

    /**
     *
     * @param int $id
     * @throws \Exception
     * @return array
     */
    private function getPreparedSettings(int $id = null): array
    {
        $piFlexform = null;
        if ($id == null) {
            return $this->prepareSettings($this->settings);
        } else {
            $qb = $this->getQueryBuilder('tt_content');
            $s = $qb->select('list_type', 'pi_flexform')
                ->from('tt_content')
                ->where($qb->expr()
                    ->eq('uid', $qb->createNamedParameter($id)))
                ->executeQuery();
            // debug($qb->getSql());
            if ($row = $s->fetchAssociative()) {
                $contentElement = $row;
            } else {
                throw new \Exception("Content element $id found.");
            }
            if ($row = $s->fetchAssociative()) {
                throw new \Exception("Two content elements with $id found? Database corrupt?");
            }

            if ($contentElement['list_type'] == PersonalDutyRosterController::LIST_TYPE) {
                $piFlexform = $contentElement['pi_flexform'];
            }
        }

        $settings = GeneralUtility::xml2array($piFlexform)['data']['sDEF']['lDEF'];
        $return = [];
        $return[PersonalDutyRosterController::CAN_VIEW_MEMBERS] = $settings['settings.' . PersonalDutyRosterController::CAN_VIEW_MEMBERS]['vDEF'];
        $return[PersonalDutyRosterController::CAN_VIEW_CURRENTLY_OFF_DUTY] = $settings['settings.' . PersonalDutyRosterController::CAN_VIEW_CURRENTLY_OFF_DUTY]['vDEF'];
        $return[PersonalDutyRosterController::PERSONAL_DUTY_ROSTER_GROUPS] = $settings['settings.' . PersonalDutyRosterController::PERSONAL_DUTY_ROSTER_GROUPS]['vDEF'];
        $return[PersonalDutyRosterController::OPTIONAL_HIDDEN_PERSONAL_DUTY_ROSTER_GROUPS] = $settings['settings.' . PersonalDutyRosterController::OPTIONAL_HIDDEN_PERSONAL_DUTY_ROSTER_GROUPS]['vDEF'];
        $return[PersonalDutyRosterController::DUTY_ROSTER_STORAGE_UIDS] = $settings['settings.' . PersonalDutyRosterController::DUTY_ROSTER_STORAGE_UIDS]['vDEF'];
        $return[PersonalDutyRosterController::PLANNING_STORAGE_UID] = $settings['settings.' . PersonalDutyRosterController::PLANNING_STORAGE_UID]['vDEF'];

        return $this->prepareSettings($return);
    }

    /**
     *
     * @param array $personalDutyRosterGroups
     * @param array $optionalHiddenPersonalDutyRosterGroups
     * @param bool $ignoreCurrentUserSettings
     * @return PersonalDutyRosterGroupFilterSettings[]|string[]
     */
    private function getPersonalDutyRosterFilterSettings(array $personalDutyRosterGroups = null, array $optionalHiddenPersonalDutyRosterGroups = null, bool $ignoreCurrentUserSettings = false)
    {
        if ($personalDutyRosterGroups == null) {
            $personalDutyRosterGroups = $this->settings[PersonalDutyRosterController::PERSONAL_DUTY_ROSTER_GROUPS];
        }
        if ($optionalHiddenPersonalDutyRosterGroups == null) {
            $optionalHiddenPersonalDutyRosterGroups = $this->settings[PersonalDutyRosterController::OPTIONAL_HIDDEN_PERSONAL_DUTY_ROSTER_GROUPS];
        }
        // debug($personalDutyRosterGroups, '$personalDutyRosterGroups');
        // debug($optionalUserHiddenTargetGroups, '$optionalUserHiddenTargetGroups');
        /**
         *
         * @var FrontendUserGroup $ug
         * @var FrontendUser $u
         */
        $u = $this->frontendUserRepository->findByUid($this->frontendUserService->getCurrentUserUid());

        $userHiddenDutyRosterGroups = [];
        foreach ($u->getHiddenPersonalDutyRosterGroups() as $ug) {
            if (in_array($ug->getUid(), $personalDutyRosterGroups)) {
                $userHiddenDutyRosterGroups[$ug->getUid()] = $ug;
            }
        }

        /**
         *
         * @var PersonalDutyRosterGroupFilterSettings $personalDutyRosterFilterSettings
         */
        $personalDutyRosterFilterSettings = GeneralUtility::makeInstance(PersonalDutyRosterGroupFilterSettings::class);
        if (!$ignoreCurrentUserSettings) {
            foreach ($optionalHiddenPersonalDutyRosterGroups as $optionalGroups) {
                if (in_array($optionalGroups, $personalDutyRosterGroups)) {
                    $ug = $this->frontendUserGroupRepository->findByUid($optionalGroups);
                    $personalDutyRosterFilterSettings->add($ug, !array_key_exists($ug->getUid(), $userHiddenDutyRosterGroups));
                }
            }
        }
        // debug($u);
        return [
            $personalDutyRosterGroups,
            $personalDutyRosterFilterSettings
        ];
    }

    /**
     *
     * @param int $id
     * @param bool $ignoreCurrentUserSettings
     * @throws \Exception
     * @return array
     */
    private function getCurrentUserCommitments(int $id, bool $ignoreCurrentUserSettings = false): array
    {
        if ($this->frontendUserService->isLogged()) {
            $this->settings = $this->getPreparedSettings($id);

            $personalDutyRosterGroups = $this->settings[PersonalDutyRosterController::PERSONAL_DUTY_ROSTER_GROUPS];
            $optionalHiddenPersonalDutyRosterGroups = $this->settings[PersonalDutyRosterController::OPTIONAL_HIDDEN_PERSONAL_DUTY_ROSTER_GROUPS];
            $planningStorageUid = $this->settings[PersonalDutyRosterController::PLANNING_STORAGE_UID];
            $dutyRosterStorageUids = $this->settings[PersonalDutyRosterController::DUTY_ROSTER_STORAGE_UIDS];

            list($personalDutyRosterGroups, $personalDutyRosterFilterSettings) = $this->getPersonalDutyRosterFilterSettings($personalDutyRosterGroups, $optionalHiddenPersonalDutyRosterGroups, $ignoreCurrentUserSettings);

            // debug($hiddenTargetGroups);
            // debug($this->getTargetGroups($settings));
            return $this->commitmentRepository->findCurrentEventCommitments($this->frontendUserService->getCurrentUser(), $dutyRosterStorageUids, $planningStorageUid, $personalDutyRosterGroups, $personalDutyRosterFilterSettings, new \DateTime(), false);
        } else {
            throw new \Exception('You ar not logged in');
        }
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
     * @param string $value
     * @return array
     */
    private function toIntArray(string $value): array
    {
        return GeneralUtility::intExplode(',', $value);
    }
}