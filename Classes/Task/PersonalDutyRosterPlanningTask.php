<?php
namespace Cylancer\Participants\Task;

use Cylancer\Participants\Domain\PresentState;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Cylancer\Participants\Domain\Model\Commitment;
use Cylancer\Participants\Domain\Repository\EventRepository;
use Cylancer\Participants\Domain\Model\Event;
use Cylancer\Participants\Domain\Repository\CommitmentRepository;
use Cylancer\Participants\Service\FrontendUserService;
use Cylancer\Participants\Domain\Repository\FrontendUserGroupRepository;
use Cylancer\Participants\Domain\Model\FrontendUserGroup;
use Cylancer\Participants\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Cylancer\Participants\Domain\Repository\FrontendUserRepository;

class PersonalDutyRosterPlanningTask extends AbstractTask
{

    // ------------------------------------------------------
    // input fields
    const PLANNING_STORAGE_UID = 'planningStorageUid';

    /** @var int */
    public $planningStorageUid = 0;

    const DUTY_ROSTER_STORAGE_UIDS = 'dutyRosterStorageUids';

    /** @var string */
    public $_dutyRosterStorageUids = '';

    /** @var array */
    public $dutyRosterStorageUids = [];

    const FE_USER_STORAGE_UIDS = 'feUserStorageUids';

    /** @var string */
    public $_feUserStorageUids = 0;
    /** @var array */
    public $feUserStorageUids = [];

    const FE_USERGROUP_STORAGE_UIDS = 'feUsergroupStorageUids';

    /** @var string */
    public $_feUsergroupStorageUids = 0;
    /** @var array */
    public $feUsergroupStorageUids = [];

    const SPECIFIED_USER_UIDS = 'specifiedUserUids';

    /** @var string */
    public $_specifiedUserUids = '';
    /** @var array */
    public $specifiedUserUids = [];

    const RESET_USERS = 'resetUsers';

    /** @var string */
    public $_resetUser = '';

    /** @var bool */
    public $resetUser = false;

    const ENABLE_REMINDER = 'enableReminder';

    public $enableReminder = '';

    const PERSONAL_DUTY_ROSTER_PAGE_UID = 'personalDutyRosterPageUid';

    /** @var int */
    public $personalDutyRosterPageUid = 0;

    const SITE_IDENTIFIER = 'siteIdentifier';

    /** @var string */
    public $siteIdentifier = '';


    // ------------------------------------------------------
    // debug switch
    const DISABLE_PERSISTENCE_MANAGER = false;

    // ------------------------------------------------------
    // internal constants
    const CURRENTLY_OFF_DUTY = 'currentlyOfDuty';

    const EXTENSION_NAME = 'Participants';

    // ------------------------------------------------------

    /** @var FrontendUserService **/
    private $frontendUserService = null;

    /** @var FrontendUserRepository     */
    private $frontendUserRepository = null;

    /** @var EventRepository  */
    private $eventRepository = null;

    /** @var CommitmentRepository */
    private $commitmentRepository = null;

    /** @var PageRepository */
    private $pageRepository = null;

    /**
     * var FrontendUserGroupRepository
     */
    private $frontendUserGroupRepository = null;

    /** @var PersistenceManager     */
    private $persistenceManager = null;

    /** @var array  */
    private $frontendUserGroupStructure = null;

    /**
     *
     * @var array
     */
    private $targetGroups = null;

    private function initialize()
    {
        $this->planningStorageUid = intval($this->planningStorageUid);
        $this->dutyRosterStorageUids = GeneralUtility::intExplode(',', $this->_dutyRosterStorageUids);
        $this->feUserStorageUids = GeneralUtility::intExplode(',', $this->_feUserStorageUids);
        $this->feUsergroupStorageUids = GeneralUtility::intExplode(',', $this->_feUsergroupStorageUids);
        $this->specifiedUserUids = GeneralUtility::intExplode(',', $this->_specifiedUserUids, TRUE);
        $this->resetUser = boolval($this->_resetUser);


        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);

        $this->frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
        $this->frontendUserRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->frontendUserRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($this->feUserStorageUids);
        $this->frontendUserRepository->setDefaultQuerySettings($querySettings);

        $this->frontendUserGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);
        $this->frontendUserGroupRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->frontendUserGroupRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($this->feUsergroupStorageUids);
        $this->frontendUserGroupRepository->setDefaultQuerySettings($querySettings);

        $this->eventRepository = GeneralUtility::makeInstance(EventRepository::class);
        $this->eventRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->eventRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($this->dutyRosterStorageUids);
        $this->eventRepository->setDefaultQuerySettings($querySettings);

        $this->commitmentRepository = GeneralUtility::makeInstance(CommitmentRepository::class);
        $this->commitmentRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->commitmentRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds([
            $this->planningStorageUid
        ]);
        $this->commitmentRepository->setDefaultQuerySettings($querySettings);

        $this->frontendUserService = GeneralUtility::makeInstance(FrontendUserService::class, $this->frontendUserRepository);

        $tmp = array();
        foreach ($this->frontendUserGroupRepository->findAll() as $ug) {
            $tmp[$ug->getUid()] = $this->frontendUserService->getSubGroups($ug);
        }
        $this->frontendUserGroupStructure = $tmp;

    }

    private function validate(): bool
    {
        $valid = true;

        $valid &= $this->pageRepository != null;
        $valid &= $this->commitmentRepository != null;
        $valid &= $this->frontendUserRepository != null;
        $valid &= $this->frontendUserGroupRepository != null;
        $valid &= $this->eventRepository != null;
        $valid &= $this->frontendUserService != null;
        if ($valid) {
            $valid &= $this->isPageUidValid($this->planningStorageUid);
            $valid &= $this->isPageUidValid($this->personalDutyRosterPageUid);
            $valid &= $this->isSiteIdentifierValid($this->siteIdentifier);
            foreach ($this->dutyRosterStorageUids as $dr) {
                $valid &= $this->isPageUidValid($dr);
            }
            foreach ($this->feUserStorageUids as $p) {
                $valid &= $this->isPageUidValid($p);
            }
            foreach ($this->feUsergroupStorageUids as $p) {
                $valid &= $this->isPageUidValid($p);
            }
            foreach ($this->specifiedUserUids as $u) {
                $valid &= $this->isUserUidValid($u);
            }
        }

        return $valid;
    }

    /**
     *
     * @param int $id
     */
    private function isPageUidValid(int $id)
    {
        return $this->pageRepository->getPage($id) != null;
    }

    /**
     *
     * @param int $id
     */
    private function isUserUidValid(int $uid)
    {
        return $this->frontendUserRepository->findByUid($uid) != null;
    }


    /**
     *
     * @return boolean
     */
    private function isSiteIdentifierValid(string $siteIdentifier): bool
    {
        try {
            GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($siteIdentifier);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }



    private $eventGroupAssociation = [];

    private function calculatePlanningPresent(FrontendUser $user, Event $event): bool
    {

        /** @var FrontendUserGroup $ug   */
        if (!array_key_exists($event->getUid(), $this->eventGroupAssociation)) {

            $this->eventGroupAssociation[$event->getUid()] = array_merge(
                //
                array_map('\Cylancer\Participants\Service\FrontendUserService::getUid', $event->getUsergroups()->toArray()),
                //
                array_map('\Cylancer\Participants\Service\FrontendUserService::getUid', $event->getEventType()
                    ->getUsergroups()
                    ->toArray())
            );
        }
        // debug($this->eventGroupAssociation);
        // debug($this->frontendUserGroupStructure);
        /**
         * 1. iterate over all user groups
         * 2. get the path of the user group to the root (over the subgroups)
         * 3. has the event groups and the user group path an intersect: return true...
         */
        foreach ($user->getUsergroup() as $ug) {
            if (!empty(array_intersect($this->frontendUserGroupStructure[$ug->getUid()], $this->eventGroupAssociation[$event->getUid()]))) {
                return true;
            }
        }
        return false;
    }

    private function eventReminder(): void
    {
        $reminderUsers = array();
        /** @var Event $event **/
        foreach ($this->eventRepository->findTomorrowsEvents() as $event) {
            /** @var FrontendUser $frontendUser **/
            foreach ($this->commitmentRepository->getEventCommitments(PresentState::PRESENT, $this->planningStorageUid, $event->getUid()) as $frontendUserUid => $data) {

                $frontendUser = $this->frontendUserRepository->findByUid($frontendUserUid);
                // debug($frontendUser, 'eventReminder()');
                if ($frontendUser->getPersonalDutyEventReminder()) {
                    $reminderUsers[$frontendUserUid]['user'] = $frontendUser;
                    $reminderUsers[$frontendUserUid]['events'][] = $event;
                }
            }
        }
        foreach ($reminderUsers as $frontendUserUid => $data) {
            $frontendUser = $data['user'];
            $fluidEmail = GeneralUtility::makeInstance(FluidEmail::class);
            $fluidEmail
                ->setRequest($this->createRequest($this->siteIdentifier))
                ->to(new Address($frontendUser->getEmail(), $frontendUser->getFirstName() . ' ' . $frontendUser->getLastName()))
                ->from(new Address(MailUtility::getSystemFromAddress(),LocalizationUtility::translate('task.personalDutyRosterPlanning.reminderMail.senderName', PersonalDutyRosterPlanningTask::EXTENSION_NAME)))
                ->subject(LocalizationUtility::translate('task.personalDutyRosterPlanning.reminderMail.subject', PersonalDutyRosterPlanningTask::EXTENSION_NAME))
                ->format(FluidEmail::FORMAT_BOTH) // send HTML and plaintext mail
                ->setTemplate('TommorrowsEventsReminderMail')
                ->assign('user', $frontendUser)
                ->assign('events', $data['events'])
                ->assign('pageUid', $this->personalDutyRosterPageUid)
            ;
            GeneralUtility::makeInstance(MailerInterface::class)->send($fluidEmail);
        }
    }



    private function createRequest(string $siteIdentifier): RequestInterface
    {
        $serverRequestFactory = GeneralUtility::makeInstance(ServerRequestFactoryInterface::class);
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($siteIdentifier);
        $serverRequest = $serverRequestFactory->createServerRequest('GET', $site->getBase())
            ->withAttribute('applicationType', \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('site', $site)
            ->withAttribute('extbase', new \TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters());
        $request = GeneralUtility::makeInstance(Request::class, $serverRequest);
        //$GLOBALS['TYPO3_REQUEST'] = $request;
        if (!isset($GLOBALS['TYPO3_REQUEST'])) {
            $GLOBALS['TYPO3_REQUEST'] = $request;
        }
        return $request;
    }



    public function execute()
    {
        $this->initialize();

        if ($this->validate()) {
            $now = new \DateTime();

            // --------------------------
            // statistic values
            $userCount = 0;
            $loadedData = 0;
            $createdCount = 0;
            $updatedCount = 0;
            $canceledCount = 0;
            // --------------------------

            $users = array();
            if (count($this->specifiedUserUids) == 0) {
                $users = $this->frontendUserRepository->findAll();
            } else {
                foreach ($this->specifiedUserUids as $uid) {
                    $users[] = $this->frontendUserRepository->findByUid($uid);
                }
            }
            // debug($users);

            // debug($this->resetUser);
            /** @var FrontendUser $u */
            /** @var Event $e */

            foreach ($users as $u) {
                $userCount++;
                // $pages = $this->getPages();
                $inserts = array();
                $updates = array();
                $canceled = array();

                // update
                foreach ($this->commitmentRepository->findExistsFutureCommitments($u->getUid(), $this->planningStorageUid, $this->dutyRosterStorageUids) as $uids) {
                    $loadedData++;
                    // debug($uids);
                    $createdCount = 0;
                    /**
                     *
                     * @var Commitment $c
                     */
                    $c = $this->commitmentRepository->findByUid($uids['commitment']);
                    $e = $this->eventRepository->findByUid(intval($uids['event']));

                    if ($e != null && $e->getDatetime() > $now) {

                        $planningPresent = $u->getCurrentlyOffDuty() ? false : $this->calculatePlanningPresent($u, $e);

                        // debug($ds);
                        if ($e->getHidden() || $e->getDeleted()) {
                            if ($c->getPresent() !== PresentState::NOT_PRESENT) {
                                $canceled[] = $c;
                            }
                            $canceledCount++;
                            $c->setEvent($e); // <-- for the template
                            $c->setPresent(PresentState::NOT_PRESENT);
                            $c->setPresentDefault(false);
                            if (PersonalDutyRosterPlanningTask::DISABLE_PERSISTENCE_MANAGER) {
                                debug($c, "CANCELED:");
                            }
                            $this->commitmentRepository->update($c);
                        } else if ($this->resetUser || $c->getPresentDefault() != $planningPresent) {
                            $updatedCount++;
                            if (($c->getPresent() === PresentState::UNKNOWN) || (($c->getPresent() === PresentState::PRESENT) xor $planningPresent)) {
                                $updates[] = $c;
                            }
                            $c->setPresentDefault($planningPresent);
                            $c->setPresent(
                                ($u->getApplyPlanningData() && $planningPresent)
                                ? PresentState::PRESENT
                                : $c->getPresent()
                            );
                            if (PersonalDutyRosterPlanningTask::DISABLE_PERSISTENCE_MANAGER) {
                                debug($c, "UPDATE:");
                            }
                            $this->commitmentRepository->update($c);
                        }
                    }
                }

                // create
                foreach ($this->commitmentRepository->findMissingCommitmentsOf($u->getUid(), $this->planningStorageUid, $this->dutyRosterStorageUids) as $eventUid) {
                    $e = $this->eventRepository->findByUid($eventUid);
                    if ($e != null) {
                        $createdCount++;
                        /**
                         *
                         * @var Commitment $c
                         */
                        $c = GeneralUtility::makeInstance(Commitment::class);
                        if ($e->getDateTime() > $now) {
                            $c->setEvent($e);
                            $c->setUser($u);

                            $planningPresent = $u->getCurrentlyOffDuty() ? false : $this->calculatePlanningPresent($u, $e);

                            $c->setPresent(
                                ($u->getApplyPlanningData() && $planningPresent)
                                ? PresentState::PRESENT
                                : PresentState::UNKNOWN
                            );
                            $c->setPresentDefault($planningPresent);
                            $c->setPid($this->planningStorageUid);
                            if (PersonalDutyRosterPlanningTask::DISABLE_PERSISTENCE_MANAGER) {
                                debug($c, "CREATE");
                            }
                            $this->commitmentRepository->add($c);
                            if ($c->getPresentDefault()) {
                                $inserts[] = $c;
                            }
                        }
                    }
                }

                if ($u->getInfoMailWhenPersonalDutyRosterChanged()) {
                    $this->sendInfoMail($u, $inserts, $updates, $canceled);
                }
            }

            if (!PersonalDutyRosterPlanningTask::DISABLE_PERSISTENCE_MANAGER) {
                $this->persistenceManager->persistAll();
            }

            $this->eventReminder();

            return true;
        } else {
            return false;
        }
    }

    private function sendInfoMail(FrontendUser $user, array $inserts, array $updates, array $canceled)
    {
        //  debug($inserts, 'I');
        //  debug($updates, 'U');
        //  debug($canceled, 'C');
        if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) && (!empty($inserts) || !empty($updates) || !empty($canceled))) {


            $fluidEmail = GeneralUtility::makeInstance(FluidEmail::class);
            $fluidEmail
                ->setRequest($this->createRequest($this->siteIdentifier))
                ->to(new Address($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()))
                ->from(new Address(MailUtility::getSystemFromAddress(), LocalizationUtility::translate('task.personalDutyRosterPlanning.updateMail.senderName', PersonalDutyRosterPlanningTask::EXTENSION_NAME)))
                ->subject(LocalizationUtility::translate('task.personalDutyRosterPlanning.updateMail.subject', PersonalDutyRosterPlanningTask::EXTENSION_NAME))
                ->format(FluidEmail::FORMAT_BOTH) // send HTML and plaintext mail
                ->setTemplate('UpdatePersonalDutyRosterPlanningMail')
                ->assign('user', $user)
                ->assign('pageUid', $this->personalDutyRosterPageUid)
            ;
            if (!empty($inserts)) {
                $fluidEmail->assign('inserts',$inserts);
            }
            if (!empty($updates)) {
                $fluidEmail->assign('updates',$updates);
            }
            if (!empty($canceled)) {
                $fluidEmail->assign('canceled',$canceled);
            }
            GeneralUtility::makeInstance(MailerInterface::class)->send($fluidEmail);
        }
    }

    /**
     * This method returns the sleep duration as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
        return 'Duty roster stroarge uids:' . $this->_dutyRosterStorageUids . //
            ' / planning storage uid: ' . $this->planningStorageUid . //
            ' / frontend user storage uids: ' . $this->_feUserStorageUids . //
            ' / frontend user group storage uids: ' . $this->_feUsergroupStorageUids . //
            ' / specified user uids:' . $this->_specifiedUserUids . //
            ' / enable reminder:' . $this->enableReminder . //
            ' / personal duty roster page uid:' . $this->personalDutyRosterPageUid .
            ' / site identifier:' . $this->siteIdentifier;
    }

    /**
     *
     * @param string $key
     * @throws \Exception
     * @return number|string
     */
    public function get(string $key)
    {
        switch ($key) {
            case PersonalDutyRosterPlanningTask::RESET_USERS:
                return $this->_resetUser;
            case PersonalDutyRosterPlanningTask::ENABLE_REMINDER:
                return $this->enableReminder;
            case PersonalDutyRosterPlanningTask::PERSONAL_DUTY_ROSTER_PAGE_UID:
                return $this->personalDutyRosterPageUid;
            case PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS:
                return $this->_specifiedUserUids;
            case PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS:
                return $this->_dutyRosterStorageUids;
            case PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID:
                return $this->planningStorageUid;
            case PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS:
                return $this->_feUserStorageUids;
            case PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS:
                return $this->_feUsergroupStorageUids;
            case PersonalDutyRosterPlanningTask::SITE_IDENTIFIER:
                return $this->siteIdentifier;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }

    /**
     *
     * @param string $key
     * @param string|number $value
     * @throws \Exception
     */
    public function set(string $key, $value)
    {
        switch ($key) {
            case PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS:
                $this->_dutyRosterStorageUids = $value;
                break;
            case PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID:
                $this->planningStorageUid = $value;
                break;
            case PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS:
                $this->_feUserStorageUids = $value;
                break;
            case PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS:
                $this->_feUsergroupStorageUids = $value;
                break;
            case PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS:
                $this->_specifiedUserUids = $value;
                break;
            case PersonalDutyRosterPlanningTask::RESET_USERS:
                $this->_resetUser = $value;
                break;
            case PersonalDutyRosterPlanningTask::ENABLE_REMINDER:
                $this->enableReminder = $value;
                break;
            case PersonalDutyRosterPlanningTask::PERSONAL_DUTY_ROSTER_PAGE_UID:
                $this->personalDutyRosterPageUid = $value;
                break;
            case PersonalDutyRosterPlanningTask::SITE_IDENTIFIER:
                $this->siteIdentifier = $value;
                break;

            default:
                throw new \Exception("Unknown key: $key");
        }
    }
}