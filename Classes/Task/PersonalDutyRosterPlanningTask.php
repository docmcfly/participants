<?php
namespace Cylancer\Participants\Task;

use Cylancer\Participants\Domain\PresentState;
use Cylancer\Participants\Domain\Model\Commitment;
use Cylancer\Participants\Domain\Repository\EventRepository;
use Cylancer\Participants\Domain\Model\Event;
use Cylancer\Participants\Domain\Repository\CommitmentRepository;
use Cylancer\Participants\Service\FrontendUserService;
use Cylancer\Participants\Domain\Repository\FrontendUserGroupRepository;
use Cylancer\Participants\Domain\Model\FrontendUserGroup;
use Cylancer\Participants\Domain\Model\FrontendUser;
use Cylancer\Participants\Domain\Repository\FrontendUserRepository;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;

class PersonalDutyRosterPlanningTask extends AbstractTask
{

    // ------------------------------------------------------
    // input fields
    public const PLANNING_STORAGE_UID = 'planningStorageUid';

    public int|string $planningStorageUid = 0;

    public const DUTY_ROSTER_STORAGE_UIDS = 'dutyRosterStorageUids';

    public array|string $dutyRosterStorageUids = [];
    private function getDutyRosterStorageUids(): array
    {
        if (is_string($this->dutyRosterStorageUids)) {
            $this->dutyRosterStorageUids = $this->intExplode($this->dutyRosterStorageUids);
        }
        return $this->dutyRosterStorageUids;
    }

    public const FE_USER_STORAGE_UIDS = 'feUserStorageUids';

    public array|string $feUserStorageUids = [];

    private function getFeUserStorageUids(): array
    {
        if (is_string($this->feUserStorageUids)) {
            $this->feUserStorageUids = $this->intExplode($this->feUserStorageUids);
        }
        return $this->feUserStorageUids;
    }

    public const FE_USERGROUP_STORAGE_UIDS = 'feUsergroupStorageUids';

    public array|string $feUsergroupStorageUids = [];
    private function getFeUsergroupStorageUids(): array
    {
        if (is_string($this->feUsergroupStorageUids)) {
            $this->feUsergroupStorageUids = $this->intExplode($this->feUsergroupStorageUids);
        }
        return $this->feUsergroupStorageUids;
    }

    public const SPECIFIED_USER_UIDS = 'specifiedUserUids';
    public array|string $specifiedUserUids = [];
    private function getSpecifiedUserUids(): array
    {
        if (is_string($this->specifiedUserUids)) {
            $this->specifiedUserUids = $this->intExplode($this->specifiedUserUids);
        }
        return $this->specifiedUserUids;
    }

    public const RESET_USERS = 'resetUsers';

    public bool|string $resetUser = false;

    public const ENABLE_REMINDER = 'enableReminder';

    public bool|string $enableReminder = '';

    public const PERSONAL_DUTY_ROSTER_PAGE_UID = 'personalDutyRosterPageUid';
    public int|string $personalDutyRosterPageUid = 0;

    public const SITE_IDENTIFIER = 'siteIdentifier';
    public string $siteIdentifier = '';


    // ------------------------------------------------------
    // debug switch
    private const DISABLE_PERSISTENCE_MANAGER = false;

    // ------------------------------------------------------
    // internal constants
    private const CURRENTLY_OFF_DUTY = 'currentlyOfDuty';

    private const EXTENSION_NAME = 'Participants';

    // ------------------------------------------------------
    private ?FrontendUserService $frontendUserService = null;

    private ?FrontendUserRepository $frontendUserRepository = null;

    private ?EventRepository $eventRepository = null;

    private ?CommitmentRepository $commitmentRepository = null;

    private ?PageRepository $pageRepository = null;
    private ?FrontendUserGroupRepository $frontendUserGroupRepository = null;

    private ?PersistenceManager $persistenceManager = null;

    private ?array $frontendUserGroupStructure = [];

    private ?array $targetGroups = [];

    private function initialize()
    {
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);

        $this->frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
        $this->frontendUserRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->frontendUserRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($this->getFeUserStorageUids());
        $this->frontendUserRepository->setDefaultQuerySettings($querySettings);

        $this->frontendUserGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);
        $this->frontendUserGroupRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->frontendUserGroupRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($this->getFeUsergroupStorageUids());
        $this->frontendUserGroupRepository->setDefaultQuerySettings($querySettings);

        $this->eventRepository = GeneralUtility::makeInstance(EventRepository::class);
        $this->eventRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->eventRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds($this->getDutyRosterStorageUids());
        $this->eventRepository->setDefaultQuerySettings($querySettings);

        $this->commitmentRepository = GeneralUtility::makeInstance(CommitmentRepository::class);
        $this->commitmentRepository->injectPersistenceManager($this->persistenceManager);
        $querySettings = $this->commitmentRepository->createQuery()->getQuerySettings();
        $querySettings->setStoragePageIds([
            $this->planningStorageUid
        ]);
        $this->commitmentRepository->setDefaultQuerySettings($querySettings);

        $this->frontendUserService = GeneralUtility::makeInstance(
            FrontendUserService::class,
            $this->frontendUserRepository,
            GeneralUtility::makeInstance(ConnectionPool::class)
        );

        $tmp = [];
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
            foreach ($this->getDutyRosterStorageUids() as $dr) {
                $valid &= $this->isPageUidValid($dr);
            }
            foreach ($this->getFeUserStorageUids() as $p) {
                $valid &= $this->isPageUidValid($p);
            }
            foreach ($this->getFeUsergroupStorageUids() as $p) {
                $valid &= $this->isPageUidValid($p);
            }
            foreach ($this->getSpecifiedUserUids() as $u) {
                $valid &= $this->isUserUidValid($u);
            }
        }
        return $valid;
    }

    private function isPageUidValid(int $id): bool
    {

        return $this->pageRepository->getPage($id) != null;
    }

    private function isUserUidValid(int $uid): bool
    {
        return $this->frontendUserRepository->findByUid($uid) != null;
    }

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
        $reminderUsers = [];
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
                ->from(new Address(MailUtility::getSystemFromAddress(), LocalizationUtility::translate('task.personalDutyRosterPlanning.reminderMail.senderName', PersonalDutyRosterPlanningTask::EXTENSION_NAME)))
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

    private function createRequest(string $siteIdentifier): ServerRequest
    {
        $serverRequestFactory = GeneralUtility::makeInstance(ServerRequestFactoryInterface::class);
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($siteIdentifier);
        $serverRequest = $serverRequestFactory->createServerRequest('GET', $site->getBase())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('site', $site)
            ->withAttribute('extbase', GeneralUtility::makeInstance(ExtbaseRequestParameters::class))
        ;
        return $serverRequest;
    }

    public function execute(): bool
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

            $users = [];
            if (count($this->getSpecifiedUserUids()) == 0) {
                $users = $this->frontendUserRepository->findAll();
            } else {
                foreach ($this->getSpecifiedUserUids() as $uid) {
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
                $inserts = [];
                $updates = [];
                $canceled = [];

                // update
                foreach ($this->commitmentRepository->findExistsFutureCommitments($u->getUid(), $this->planningStorageUid, $this->getDutyRosterStorageUids()) as $uids) {
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
                foreach ($this->commitmentRepository->findMissingCommitmentsOf($u->getUid(), $this->planningStorageUid, $this->getDutyRosterStorageUids()) as $eventUid) {
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

    private function sendInfoMail(FrontendUser $user, array $inserts, array $updates, array $canceled): void
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
                $fluidEmail->assign('inserts', $inserts);
            }
            if (!empty($updates)) {
                $fluidEmail->assign('updates', $updates);
            }
            if (!empty($canceled)) {
                $fluidEmail->assign('canceled', $canceled);
            }
            GeneralUtility::makeInstance(MailerInterface::class)->send($fluidEmail);
        }
    }

    /**
     * This method returns the sleep duration as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation(): string
    {
        return 'Duty roster stroarge uids:' . $this->get(PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS) . //
            ' / planning storage uid: ' . $this->get(PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID) . //
            ' / frontend user storage uids: ' . $this->get(PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS) . //
            ' / frontend user group storage uids: ' . $this->get(PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS) . //
            ' / specified user uids:' . $this->get(PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS) . //
            ' / enable reminder:' . $this->enableReminder . //
            ' / personal duty roster page uid:' . $this->personalDutyRosterPageUid .
            ' / site identifier:' . $this->siteIdentifier;
    }

    public function get(string $key): mixed
    {
        switch ($key) {
            case PersonalDutyRosterPlanningTask::RESET_USERS:
                return boolval($this->resetUser);
            case PersonalDutyRosterPlanningTask::ENABLE_REMINDER:
                return boolval($this->enableReminder);

            case PersonalDutyRosterPlanningTask::PERSONAL_DUTY_ROSTER_PAGE_UID:
                return intval($this->personalDutyRosterPageUid);
            case PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID:
                return intval($this->planningStorageUid);

            case PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS:
                return implode(',', $this->getSpecifiedUserUids());
            case PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS:
                return implode(',', $this->getDutyRosterStorageUids());
            case PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS:
                return implode(',', $this->getFeUserStorageUids());
            case PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS:
                return implode(',', $this->getFeUsergroupStorageUids());

            case PersonalDutyRosterPlanningTask::SITE_IDENTIFIER:
                return $this->siteIdentifier;
            default:
                throw new \Exception("Unknown key: $key");
        }
    }

    private function intExplode(string|array $value): array
    {
        return is_string(trim($value))
            ? (strlen(trim($value)) > 0
                ? GeneralUtility::intExplode(',', $value)
                : [])
            : $value;
    }


    public function set(array $data): void
    {
        foreach ([ // 
            PersonalDutyRosterPlanningTask::RESET_USERS, //
            PersonalDutyRosterPlanningTask::ENABLE_REMINDER, //

            PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID,  //
            PersonalDutyRosterPlanningTask::PERSONAL_DUTY_ROSTER_PAGE_UID, //

            PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS, //
            PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS, //
            PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS, //
            PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS, //

            PersonalDutyRosterPlanningTask::SITE_IDENTIFIER//
        ] as $key) {
            $value = $data[$key];
            switch ($key) {

                case PersonalDutyRosterPlanningTask::RESET_USERS:
                    $this->resetUser = boolval($value);
                    break;
                case PersonalDutyRosterPlanningTask::ENABLE_REMINDER:
                    $this->enableReminder = boolval($value);
                    break;
                case PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID:
                    $this->planningStorageUid = intval($value);
                    break;
                case PersonalDutyRosterPlanningTask::PERSONAL_DUTY_ROSTER_PAGE_UID:
                    $this->personalDutyRosterPageUid = intval($value);
                    break;

                case PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS:
                    $this->specifiedUserUids = $this->intExplode($value);
                    break;
                case PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS:
                    $this->dutyRosterStorageUids = $this->intExplode($value);
                    break;
                case PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS:
                    $this->feUserStorageUids = $this->intExplode($value);
                    break;
                case PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS:
                    $this->feUsergroupStorageUids = $this->intExplode($value);
                    break;

                case PersonalDutyRosterPlanningTask::SITE_IDENTIFIER:
                    $this->siteIdentifier = $value;
                    break;

                default:
                    throw new \Exception("Unknown key: $key");
            }
        }
    }

    /**
     * 
     * @deprecated remove if all instances with the correct types are saved.
     * @return bool
     */
    public function save(): bool
    {
        $this->resetUser = boolval($this->resetUser);
        $this->enableReminder = boolval($this->enableReminder);
        $this->personalDutyRosterPageUid = intval($this->personalDutyRosterPageUid);
        $this->planningStorageUid = intval($this->planningStorageUid);
        $this->getSpecifiedUserUids();
        $this->getDutyRosterStorageUids();
        $this->getFeUserStorageUids();
        $this->getFeUsergroupStorageUids();

        return parent::save();

    }


}