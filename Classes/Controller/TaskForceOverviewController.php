<?php
namespace Cylancer\Participants\Controller;

use Cylancer\Participants\Domain\Repository\FrontendUserGroupRepository;
use Psr\Http\Message\ResponseInterface;
use Cylancer\Participants\Domain\Repository\TimeOutRepository;
use Cylancer\Participants\Domain\Repository\CommitmentRepository;
use Cylancer\Participants\Domain\Repository\FrontendUserRepository;
use Cylancer\Participants\Domain\Model\TimeOut;
use Cylancer\Participants\Service\FrontendUserService;
use Cylancer\Participants\Domain\Model\FrontendUser;
use Cylancer\Participants\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
class TaskForceOverviewController extends ActionController
{

    private const TASK_FORCE_LIST = 'taskForceList';

    private const USER_PROFILE = 'userProfile';

    private const LOCAL_FIRE_CHIEF = 'localFireChief';

    private const DEPUTY_LOCAL_FIRE_CHIEF = 'deputyLocalFireChief';

    private const FIRE_DEPARTMENT_GROUP_LEADER = 'fireDepartmentGroupLeader';

    private const SPECIAL_FORCES = 'specialForces';

    private const SPECIAL_FORCES_COLOR = 'specialForcesColor';

    private const FIRE_DEPARTMENT_GROUP_1 = 'fireDepartmentGroup1';

    private const FIRE_DEPARTMENT_GROUP_2 = 'fireDepartmentGroup2';

    private const CURRENT_FIRE_DEPARTMENT_GROUP = 'currentFireDepartmentGroup';

    private const CURRENT_FIRE_DEPARTMENT_GROUP_COLOR = 'currentFireDepartmentGroupColor';

    private const TIME_OUTS = 'timeOuts';

    private const WITHOUT_DIGITAL_SIGNAL_UNIT = 'withoutDigitalSignalingUnit';

    private const CAN_VIEW_CURRENTLY_OFF_DUTY = 'canViewCurrentlyOffDuty';
    public function __construct(
        private readonly FrontendUserService $frontendUserService,
        private readonly TimeOutRepository $timeOutRepository,
        private readonly CommitmentRepository $commitmentRepository, //
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly FrontendUserGroupRepository $frontendUserGroupRepository
    ) {
    }

    public function showAction(): ResponseInterface
    {
        $this->view->assign(TaskForceOverviewController::TASK_FORCE_LIST . 'Link', $this->settings[TaskForceOverviewController::TASK_FORCE_LIST]);
        $this->view->assign(TaskForceOverviewController::USER_PROFILE . 'Link', $this->settings[TaskForceOverviewController::USER_PROFILE]);

        $lfc = $this->frontendUserRepository->findByUserGroups($this->settings[TaskForceOverviewController::LOCAL_FIRE_CHIEF])[0];
        $this->view->assign(TaskForceOverviewController::LOCAL_FIRE_CHIEF, $lfc);

        $dlfc = $this->frontendUserRepository->findByUserGroups($this->settings[TaskForceOverviewController::DEPUTY_LOCAL_FIRE_CHIEF])[0];
        $this->view->assign(TaskForceOverviewController::DEPUTY_LOCAL_FIRE_CHIEF, $dlfc);

        $usersWithTimeout = [];
        $tmp = [];
        foreach ($this->timeOutRepository->getTimeOuts() as $to) {
            $usersWithTimeout[] = $to->getUser()->getUid();
            $tmp[$to->getReason()][] = $to;
        }

        $usersWithTimeout = \array_unique($usersWithTimeout);
        $timeOuts = [];
        foreach ($tmp as $reason => $to) {
            $tmpUsersTimeOuts = [];
            foreach ($to as $_to) {
                if (array_key_exists($_to->getUser()->getUid(), $tmpUsersTimeOuts)) {
                    if ($_to->getUntil() > $tmpUsersTimeOuts[$_to->getUser()->getUid()]->getUntil()) {
                        $tmpUsersTimeOuts[$_to->getUser()->getUid()] = $_to;
                    }
                } else {
                    $tmpUsersTimeOuts[$_to->getUser()->getUid()] = $_to;
                }
            }
            foreach ($tmpUsersTimeOuts as $_to) {
                $timeOuts[$reason][] = $_to;
            }
        }

        $canViewCurrentlyOfDuty = false;
        if ($this->frontendUserService->isLogged() && $this->settings[TaskForceOverviewController::CAN_VIEW_CURRENTLY_OFF_DUTY] != null) {
            /** @var FrontendUserGroup $frontendUserGroup */
            foreach ($this->frontendUserService->getCurrentUser()->getUsergroup() as $frontendUserGroup) {
                if (\in_array($this->settings[TaskForceOverviewController::CAN_VIEW_CURRENTLY_OFF_DUTY], $this->frontendUserService->getSubGroups($frontendUserGroup))) {
                    $canViewCurrentlyOfDuty = true;
                    break;
                }
            }
        }

        if ($canViewCurrentlyOfDuty) {
            $currentlyOffDutyUsers = $this->frontendUserRepository->findByCurrentlyOffDuty(true);
            /** @var FrontendUser $codu */
            foreach ($currentlyOffDutyUsers as $codu) {
                if ($this->isInTheFuture($codu->getCurrentlyOffDutyUntil())) {
                    $to = new TimeOut();
                    $to->setUser($codu);
                    $to->setUntil($codu->getCurrentlyOffDutyUntil());
                    $timeOuts[LocalizationUtility::translate('taskForceOverview.reason.currentlyOffDuty', 'Usertools')][] = $to;
                }
            }
        }

        $wdsu = TaskForceOverviewController::filter($this->frontendUserRepository->findByUserGroups($this->settings[TaskForceOverviewController::WITHOUT_DIGITAL_SIGNAL_UNIT]), $usersWithTimeout);

        $this->view->assign(TaskForceOverviewController::WITHOUT_DIGITAL_SIGNAL_UNIT . 'All', $wdsu);
        $usersWithTimeout = \array_merge($usersWithTimeout, TaskForceOverviewController::getUids($wdsu));

        if ($lfc != null) {
            $usersWithTimeout[] = $lfc->getUid();
        }
        if ($dlfc != null) {
            $usersWithTimeout[] = $dlfc->getUid();
        }
        $fdgl = TaskForceOverviewController::filter($this->frontendUserRepository->findByUserGroups($this->settings[TaskForceOverviewController::FIRE_DEPARTMENT_GROUP_LEADER]), $usersWithTimeout);
        $this->view->assign(TaskForceOverviewController::FIRE_DEPARTMENT_GROUP_LEADER . 's', $fdgl);

        $usersWithTimeout = \array_merge($usersWithTimeout, TaskForceOverviewController::getUids($fdgl));

        $this->view->assign(TaskForceOverviewController::FIRE_DEPARTMENT_GROUP_1, TaskForceOverviewController::filter($this->frontendUserRepository->findByUserGroups($this->settings[TaskForceOverviewController::FIRE_DEPARTMENT_GROUP_1]), $usersWithTimeout));
        $this->view->assign(TaskForceOverviewController::FIRE_DEPARTMENT_GROUP_2, TaskForceOverviewController::filter($this->frontendUserRepository->findByUserGroups($this->settings[TaskForceOverviewController::FIRE_DEPARTMENT_GROUP_2]), $usersWithTimeout));
        $this->view->assign(TaskForceOverviewController::TIME_OUTS, $timeOuts);
        $this->view->assign(TaskForceOverviewController::SPECIAL_FORCES, $this->frontendUserGroupRepository->findByUid(intval($this->settings[TaskForceOverviewController::SPECIAL_FORCES])));
        $this->view->assign('currentUser', $this->frontendUserService->getCurrentUser());

        $this->view->assign(TaskForceOverviewController::SPECIAL_FORCES_COLOR, '<style type="text/css">
        .specialForces {
            background-color: ' . $this->settings[TaskForceOverviewController::SPECIAL_FORCES_COLOR] . ' !important
        }
        </style>');
        $cg = \intval(date('m')) % 2;
        $this->view->assign(TaskForceOverviewController::CURRENT_FIRE_DEPARTMENT_GROUP, $cg);
        $this->view->assign(TaskForceOverviewController::FIRE_DEPARTMENT_GROUP_1 . 'Marker', $cg == 1 ? 'background-color: ' . $this->settings[TaskForceOverviewController::CURRENT_FIRE_DEPARTMENT_GROUP_COLOR] . ';' : '');
        $this->view->assign(TaskForceOverviewController::FIRE_DEPARTMENT_GROUP_2 . 'Marker', $cg == 0 ? 'background-color: ' . $this->settings[TaskForceOverviewController::CURRENT_FIRE_DEPARTMENT_GROUP_COLOR] . ';' : '');

        return $this->htmlResponse();
    }

    private static function isInTheFuture(?string $until): bool
    {
        return $until == null ? false : \DateTime::createFromFormat('Y-m-d', $until)->getTimestamp() > time();
    }

    private static function getUids(array $array): array
    {
        return array_map(function ($value) {
            return $value->getUid();
        }, $array);
    }

    private static function filter($users, array $userUids): array
    {
        $return = [];
        /** @var FrontendUser $u */
        foreach ($users as $u) {
            if (!$u->getCurrentlyOffDuty() && !\in_array($u->getUid(), $userUids)) {
                $return[] = $u;
            }
        }
        return $return;
    }
}