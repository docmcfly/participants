<?php
namespace Cylancer\Participants\Task;

use Cylancer\Participants\Domain\PresentState;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
use Cylancer\Participants\Service\EmailSendService;
use Cylancer\Participants\Domain\Repository\FrontendUserRepository;

class PersonalDutyRosterPlanningTask extends AbstractTask
{

    // ------------------------------------------------------
    // input fields
    const PLANNING_STORAGE_UID = 'planningStorageUid';

    public $planningStorageUid = 0;

    const DUTY_ROSTER_STORAGE_UIDS = 'dutyRosterStorageUids';

    public $dutyRosterStorageUids = 0;

    const FE_USER_STORAGE_UIDS = 'feUserStorageUids';

    public $feUserStorageUids = 0;

    const FE_USERGROUP_STORAGE_UIDS = 'feUsergroupStorageUids';

    public $feUsergroupStorageUids = 0;

    const SPECIFIED_USER_UIDS = 'specifiedUserUids';

    public $specifiedUserUids = '';

    const RESET_USERS = 'resetUsers';

    public $resetUser = '';

    const ENABLE_REMINDER = 'enableReminder';

    public $enableReminder = '';

    const REMINDER_TARGET_URL = 'reminderTargetUrl';

    public $reminderTargetUrl = '';

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

    /** @var EmailSendService */
    private $emailSendService = null;

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
        $this->dutyRosterStorageUids = GeneralUtility::intExplode(',', $this->dutyRosterStorageUids);
        $this->feUserStorageUids = GeneralUtility::intExplode(',', $this->feUserStorageUids);
        $this->feUsergroupStorageUids = GeneralUtility::intExplode(',', $this->feUsergroupStorageUids);
        $this->specifiedUserUids = GeneralUtility::intExplode(',', $this->specifiedUserUids, TRUE);
        $this->resetUser = boolval($this->resetUser);

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);

        $this->frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class, $objectManager);
        $this->frontendUserRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->frontendUserRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($this->feUserStorageUids);
        $this->frontendUserRepository->setDefaultQuerySettings($querySettings);

        $this->frontendUserGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class, $objectManager);
        $this->frontendUserGroupRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->frontendUserGroupRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($this->feUsergroupStorageUids);
        $this->frontendUserGroupRepository->setDefaultQuerySettings($querySettings);

        $this->eventRepository = GeneralUtility::makeInstance(EventRepository::class, $objectManager);
        $this->eventRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->eventRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($this->dutyRosterStorageUids);
        $this->eventRepository->setDefaultQuerySettings($querySettings);

        $this->commitmentRepository = GeneralUtility::makeInstance(CommitmentRepository::class, $objectManager);
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

        // debug($this->frontendUserGroupStructure);

        $this->emailSendService = GeneralUtility::makeInstance(EmailSendService::class);
    }

    private function validate(): bool
    {
        $valid = true;

        $valid &= $this->pageRepository != null;
        $valid &= $this->commitmentRepository != null;
        $valid &= $this->frontendUserRepository != null;
        $valid &= $this->frontendUserGroupRepository != null;
        $valid &= $this->eventRepository != null;
        $valid &= $this->emailSendService != null;
        $valid &= $this->frontendUserService != null;
        if ($valid) {
            $valid &= $this->isPageUidValid($this->planningStorageUid);
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
            foreach ($this->commitmentRepository->getEventMembers($this->planningStorageUid, $event->getUid()) as $frontendUserUid => $data) {

                $frontendUser = $this->frontendUserRepository->findByUid($frontendUserUid);
                // debug($frontendUser, 'eventReminder()');
                if ($frontendUser->getPersonalDutyEventReminder()) {
                    $reminderUsers[$frontendUserUid]['user'] = $frontendUser;
                    $reminderUsers[$frontendUserUid]['events'][] = $event;
                }
            }
        }
        foreach ($reminderUsers as $frontendUserUid => $data) {
            $data['reminderTargetUrl'] = $this->reminderTargetUrl;
            $frontendUser = $data['user'];
            $recipient = [
                $frontendUser->getEmail() => $frontendUser->getFirstName() . ' ' . $frontendUser->getLastName()
            ];
            $sender = [
                MailUtility::getSystemFromAddress() => LocalizationUtility::translate('task.personalDutyRosterPlanning.reminderMail.senderName', PersonalDutyRosterPlanningTask::EXTENSION_NAME)
            ];
            $subject = LocalizationUtility::translate('task.personalDutyRosterPlanning.reminderMail.subject', PersonalDutyRosterPlanningTask::EXTENSION_NAME);
            $this->emailSendService->sendTemplateEmail($recipient, $sender, [], $subject, 'TommorrowsEventsReminderMail', PersonalDutyRosterPlanningTask::EXTENSION_NAME, $data);
        }
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
                            if (($c->getPresent() !== PresentState::NOT_PRESENT) xor $planningPresent) {
                                $updates[] = $c;
                            }
                            $c->setPresentDefault($planningPresent);
                            $c->setPresent($u->getApplyPlanningData()
                                ? ($planningPresent ? PresentState::PRESENT : PresentState::NOT_PRESENT)
                                : PresentState::NOT_PRESENT);

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
                            $c->setPresent($u->getApplyPlanningData()
                                ? ($planningPresent ? PresentState::PRESENT : PresentState::NOT_PRESENT)
                                : PresentState::NOT_PRESENT);
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
        // debug($inserts, 'I');
        // debug($updates, 'U');
        // debug($canceled, 'C');
        if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL) && (!empty($inserts) || !empty($updates) || !empty($canceled))) {
            $recipient = [
                $user->getEmail() => $user->getFirstName() . ' ' . $user->getLastName()
            ];
            $sender = [
                MailUtility::getSystemFromAddress() => LocalizationUtility::translate('task.personalDutyRosterPlanning.updateMail.senderName', PersonalDutyRosterPlanningTask::EXTENSION_NAME)
            ];
            $subject = LocalizationUtility::translate('task.personalDutyRosterPlanning.updateMail.subject', PersonalDutyRosterPlanningTask::EXTENSION_NAME);

            $data = [
                'user' => $user
            ];
            if (!empty($inserts)) {
                $data['inserts'] = $inserts;
            }
            if (!empty($updates)) {
                $data['updates'] = $updates;
            }
            if (!empty($canceled)) {
                $data['canceled'] = $canceled;
            }

            $this->emailSendService->sendTemplateEmail($recipient, $sender, [], $subject, 'UpdatePersonalDutyRosterPlanningMail', PersonalDutyRosterPlanningTask::EXTENSION_NAME, $data);
        }
    }

    /**
     * This method returns the sleep duration as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
        return 'Duty roster stroarge uids:' . $this->dutyRosterStorageUids . //
            ' / planning storage uid: ' . $this->planningStorageUid . //
            ' / frontend user storage uids: ' . $this->feUserStorageUids . //
            ' / frontend user group storage uids: ' . $this->feUsergroupStorageUids . //
            ' / specified user uids:' . $this->specifiedUserUids . //
            ' / enable reminder:' . $this->enableReminder . //
            ' / reminder target url:' . $this->reminderTargetUrl;
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
                return $this->resetUser;
            case PersonalDutyRosterPlanningTask::ENABLE_REMINDER:
                return $this->enableReminder;
            case PersonalDutyRosterPlanningTask::REMINDER_TARGET_URL:
                return $this->reminderTargetUrl;
            case PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS:
                return $this->specifiedUserUids;
            case PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS:
                return $this->dutyRosterStorageUids;
            case PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID:
                return $this->planningStorageUid;
            case PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS:
                return $this->feUserStorageUids;
            case PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS:
                return $this->feUsergroupStorageUids;
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
                $this->dutyRosterStorageUids = $value;
                break;
            case PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID:
                $this->planningStorageUid = $value;
                break;
            case PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS:
                $this->feUserStorageUids = $value;
                break;
            case PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS:
                $this->feUsergroupStorageUids = $value;
                break;
            case PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS:
                $this->specifiedUserUids = $value;
                break;
            case PersonalDutyRosterPlanningTask::RESET_USERS:
                $this->resetUser = $value;
                break;
            case PersonalDutyRosterPlanningTask::ENABLE_REMINDER:
                $this->enableReminder = $value;
                break;
            case PersonalDutyRosterPlanningTask::REMINDER_TARGET_URL:
                $this->reminderTargetUrl = $value;
                break;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }
}