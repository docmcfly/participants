<?php
namespace Cylancer\Participants\Task;

use Cylancer\Participants\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;
 
class PersonalDutyRosterPlanningAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{

    /**
     *
     * @param array $taskInfo
     * @param PersonalDutyRosterPlanningTask|null $task
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @param array $additionalFields
     * @return void
     */
    private function initBooleanAddtionalField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule, String $key, array &$additionalFields)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        // Initialize extra field value
        if (empty($taskInfo[$key])) {
            if ($currentSchedulerModuleAction->equals(Action::ADD)) {
                // In case of new task and if field is empty, set default sleep time
                $taskInfo[$key] = 0;
            } elseif ($currentSchedulerModuleAction->equals(Action::EDIT)) {
                // In case of edit, set to internal value if no data was submitted already
                $taskInfo[$key] = $task->get($key);
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo[$key] = 0;
            }
        }

        $checked = $taskInfo[$key] ? 'checked="checked"' : '';
        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="hidden" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="0"><input type="checkbox" class="" ' . $checked . ' name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="1" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.' . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    /**
     *
     * @param array $taskInfo
     * @param PersonalDutyRosterPlanningTask|null $task
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @param array $additionalFields
     * @return void
     */
    private function initStringAddtionalField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule, String $key, array &$additionalFields)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        // Initialize extra field value
        if (empty($taskInfo[$key])) {
            if ($currentSchedulerModuleAction->equals(Action::ADD)) {
                // In case of new task and if field is empty, set default sleep time
                $taskInfo[$key] = '';
            } elseif ($currentSchedulerModuleAction->equals(Action::EDIT)) {
                // In case of edit, set to internal value if no data was submitted already
                $taskInfo[$key] = $task->get($key);
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo[$key] = '';
            }
        }

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.' . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    /**
     *
     * @param array $taskInfo
     * @param PersonalDutyRosterPlanningTask|null $task
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @param array $additionalFields
     * @return void
     */
    private function initIntegerAddtionalField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule, String $key, array &$additionalFields)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        // Initialize extra field value
        if (empty($taskInfo[$key])) {
            if ($currentSchedulerModuleAction->equals(Action::ADD)) {
                // In case of new task and if field is empty, set default sleep time
                $taskInfo[$key] = 0;
            } elseif ($currentSchedulerModuleAction->equals(Action::EDIT)) {
                // In case of edit, set to internal value if no data was submitted already
                $taskInfo[$key] = $task->get($key);
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo[$key] = 0;
            }
        }

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="number" min="0" max="99999" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.' . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    /**
     *
     * @param array $taskInfo
     * @param PersonalDutyRosterPlanningTask|null $task
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @param array $additionalFields
     * @return void
     */
    private function initTextAddtionalField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule, String $key, array &$additionalFields)
    {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();

        // Initialize extra field value
        if (empty($taskInfo[$key])) {
            if ($currentSchedulerModuleAction->equals(Action::ADD)) {
                // In case of new task and if field is empty, set default sleep time
                $taskInfo[$key] = '';
            } elseif ($currentSchedulerModuleAction->equals(Action::EDIT)) {
                // In case of edit, set to internal value if no data was submitted already
                $taskInfo[$key] = $task->get($key);
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo[$key] = '';
            }
        }

        // Write the code for the field
        $fieldID = 'task_' . $key;

        $fieldCode = '<textarea type="url" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" >' . $taskInfo[$key] . '</textarea>';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.' . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    /**
     * This method is used to define new fields for adding or editing a task
     * In this case, it adds a sleep time field
     *
     * @param array $taskInfo
     *            Reference to the array containing the info used in the add/edit form
     * @param PersonalDutyRosterPlanningTask|null $task
     *            When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule
     *            Reference to the calling object (Scheduler's BE module)
     * @return array Array containing all the information pertaining to the additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];

        $this->initIntegerAddtionalField($taskInfo, $task, $schedulerModule, PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID, $additionalFields);

        $this->initStringAddtionalField($taskInfo, $task, $schedulerModule, PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS, $additionalFields);

        $this->initStringAddtionalField($taskInfo, $task, $schedulerModule, PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS, $additionalFields);

        $this->initStringAddtionalField($taskInfo, $task, $schedulerModule, PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS, $additionalFields);

        $this->initStringAddtionalField($taskInfo, $task, $schedulerModule, PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS, $additionalFields);

        $this->initBooleanAddtionalField($taskInfo, $task, $schedulerModule, PersonalDutyRosterPlanningTask::RESET_USERS, $additionalFields);

        $this->initBooleanAddtionalField($taskInfo, $task, $schedulerModule, PersonalDutyRosterPlanningTask::ENABLE_REMINDER, $additionalFields);

        // debug($additionalFields);
        return $additionalFields;
    }

    /**
     *
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @return boolean
     */
    private function validatePagesAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule, String $key)
    {
        $result = true;
        $pages = GeneralUtility::intExplode(',', $submittedData[$key]);
        foreach ($pages as $uid) {
            if (! $this->validatePage($uid)) {
                $this->addMessage(str_replace('%1', $uid, $this->getLanguageService()
                    ->sL('LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.error.invalidPage.' . $key)), FlashMessage::ERROR);
                $result = false;
            }
        }

        return $result;
    }

    /**
     *
     * @param int $uid
     * @return boolean
     */
    private function validateUser(int $uid)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);
        return $frontendUserRepository->findByUid($uid) != null;
    }

    /**
     *
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @return boolean
     */
    private function validateUsersAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule, String $key, bool $emptyAllowed = false)
    {
        if ($emptyAllowed && trim($submittedData[$key]) == '') {
            return true;
        }
        $result = true;
        $userUids = GeneralUtility::intExplode(',', $submittedData[$key]);
        foreach ($userUids as $uid) {
            if (! $this->validateUser($uid)) {
                $this->addMessage(str_replace('%1', $uid, $this->getLanguageService()
                    ->sL('LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.error.invalidUser.' . $key)), FlashMessage::ERROR);
                $result = false;
            }
        }

        return $result;
    }

    /**
     *
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @return boolean
     */
    private function validatePageAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule, String $key)
    {
        $result = true;
        if (! $this->validatePage($submittedData[$key])) {
            $this->addMessage(str_replace('%1', $submittedData[$key], $this->getLanguageService()
                ->sL('LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.error.invalidPage.' . $key)), FlashMessage::ERROR);
            $result = false;
        }

        return $result;
    }

    private function validatePage($pid)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $pageRepository = $objectManager->get(PageRepository::class);
        return trim($pid) == strval(intval($pid)) && $pageRepository->getPage($pid) != null;
    }

    /**
     *
     * @param array $submittedData
     * @param SchedulerModuleController $schedulerModule
     * @param String $key
     * @return boolean
     */
    private function validateBooleanAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule, String $key)
    {
        $result = true;
        if (! ($submittedData[$key] === '0' || $submittedData[$key] === '1')) {
            $this->addMessage(str_replace('%1', $submittedData[$key], $this->getLanguageService()
                ->sL('LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.error.invalidBoolean.' . $key)), FlashMessage::ERROR);
            $result = false;
        }

        return $result;
    }

    /**
     * This method checks any additional data that is relevant to the specific task
     * If the task class is not relevant, the method is expected to return TRUE
     *
     * @param array $submittedData
     *            Reference to the array containing the data submitted by the user
     * @param SchedulerModuleController $schedulerModule
     *            Reference to the calling object (Scheduler's BE module)
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $result = true;
        $result &= $this->validatePageAdditionalField($submittedData, $schedulerModule, PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID);
        $result &= $this->validatePagesAdditionalField($submittedData, $schedulerModule, PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS);
        $result &= $this->validatePagesAdditionalField($submittedData, $schedulerModule, PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS);
        $result &= $this->validatePagesAdditionalField($submittedData, $schedulerModule, PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS);
        $result &= $this->validateUsersAdditionalField($submittedData, $schedulerModule, PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS, true);
        $result &= $this->validateBooleanAdditionalField($submittedData, $schedulerModule, PersonalDutyRosterPlanningTask::RESET_USERS);
        $result &= $this->validateBooleanAdditionalField($submittedData, $schedulerModule, PersonalDutyRosterPlanningTask::ENABLE_REMINDER);
        return $result;
    }

    /**
     *
     * @param array $submittedData
     * @param AbstractTask $task
     * @param String $key
     * @return void
     */
    public function saveAdditionalField(array $submittedData, AbstractTask $task, String $key)
    {
        /**
         *
         * @var PersonalDutyRosterPlanningTask $task
         */
        $task->set($key, $submittedData[$key]);
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches
     *
     * @param array $submittedData
     *            Array containing the data submitted by the user
     * @param PersonalDutyRosterPlanningTask $task
     *            Reference to the current task object
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $this->saveAdditionalField($submittedData, $task, PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID);
        $this->saveAdditionalField($submittedData, $task, PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS);
        $this->saveAdditionalField($submittedData, $task, PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS);
        $this->saveAdditionalField($submittedData, $task, PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS);
        $this->saveAdditionalField($submittedData, $task, PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS);
        $this->saveAdditionalField($submittedData, $task, PersonalDutyRosterPlanningTask::RESET_USERS);
        $this->saveAdditionalField($submittedData, $task, PersonalDutyRosterPlanningTask::ENABLE_REMINDER);
    }

    /**
     *
     * @return LanguageService|null
     */
    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
