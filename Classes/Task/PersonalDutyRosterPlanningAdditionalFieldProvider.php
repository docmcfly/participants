<?php
namespace Cylancer\Participants\Task;

use Cylancer\Participants\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

class PersonalDutyRosterPlanningAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{

    private const TRANSLATION_PREFIX = 'LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.';

    private function getDefault(string $key): string|int|bool
    {
        switch ($key) {
            case PersonalDutyRosterPlanningTask::RESET_USERS:
            case PersonalDutyRosterPlanningTask::ENABLE_REMINDER:
                return false;
            case PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID:
            case PersonalDutyRosterPlanningTask::PERSONAL_DUTY_ROSTER_PAGE_UID:
            case PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS:
            case PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS:
            case PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS:
            case PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS:
                return 0;
            default:
                return '';
        }
    }

    private function setCurrentKey(array &$taskInfo, ?PersonalDutyRosterPlanningTask $task, string $key): void
    {
        if (empty($taskInfo[$key])) {
            $taskInfo[$key] = $task != null ? $task->get($key) : $this->getDefault($key);
        }
    }

    private function initBooleanAddtionalField(array &$taskInfo, $task, string $key, array &$additionalFields): void
    {
        $this->setCurrentKey($taskInfo, $task, $key);

        $checked = $taskInfo[$key] ? 'checked="checked"' : '';

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="hidden" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="0"><input type="checkbox" class="" ' . $checked . ' name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="1" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    private function initStringAddtionalField(array &$taskInfo, $task, string $key, array &$additionalFields): void
    {
        $this->setCurrentKey($taskInfo, $task, $key);

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    private function initIntegerAddtionalField(array &$taskInfo, $task, string $key, array &$additionalFields): void
    {
        $this->setCurrentKey($taskInfo, $task, $key);

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="number" min="0" max="99999" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    private function initTextAddtionalField(array &$taskInfo, $task, string $key, array &$additionalFields): void
    {
        $this->setCurrentKey($taskInfo, $task, $key);


        // Write the code for the field
        $fieldID = 'task_' . $key;

        $fieldCode = '<textarea type="url" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" >' . $taskInfo[$key] . '</textarea>';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }

    private function initUrlAddtionalField(array &$taskInfo, $task, string $key, array &$additionalFields): void
    {
        $this->setCurrentKey($taskInfo, $task, $key);

        // Write the code for the field
        $fieldID = 'task_' . $key;
        $fieldCode = '<input type="url" class="form-control" name="tx_scheduler[' . $key . ']" id="' . $fieldID . '" value="' . $taskInfo[$key] . '" >';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . $key,
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
    }


    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule): array
    {
        $additionalFields = [];

        $this->initIntegerAddtionalField($taskInfo, $task, PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID, $additionalFields);

        $this->initStringAddtionalField($taskInfo, $task, PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS, $additionalFields);

        $this->initStringAddtionalField($taskInfo, $task, PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS, $additionalFields);

        $this->initStringAddtionalField($taskInfo, $task, PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS, $additionalFields);

        $this->initStringAddtionalField($taskInfo, $task, PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS, $additionalFields);

        $this->initBooleanAddtionalField($taskInfo, $task, PersonalDutyRosterPlanningTask::RESET_USERS, $additionalFields);

        $this->initBooleanAddtionalField($taskInfo, $task, PersonalDutyRosterPlanningTask::ENABLE_REMINDER, $additionalFields);

        $this->initIntegerAddtionalField($taskInfo, $task, PersonalDutyRosterPlanningTask::PERSONAL_DUTY_ROSTER_PAGE_UID, $additionalFields);

        $this->initStringAddtionalField($taskInfo, $task, PersonalDutyRosterPlanningTask::SITE_IDENTIFIER, $additionalFields);

        // debug($additionalFields);
        return $additionalFields;
    }

    private function validatePagesAdditionalField(array &$submittedData, string $key): bool
    {
        $result = true;
        $pages = GeneralUtility::intExplode(',', $submittedData[$key]);
        foreach ($pages as $uid) {
            if (!$this->validatePage($uid)) {
                $this->addMessage(str_replace('%1', $uid, $this->getLanguageService()
                    ->sL(PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalidPage.' . $key)), ContextualFeedbackSeverity::ERROR);
                $result = false;
            }
        }

        return $result;
    }

    private function validateUser(int $uid): bool
    {
        $frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
        return $frontendUserRepository->findByUid($uid) != null;
    }

    private function validateUsersAdditionalField(array &$submittedData, string $key, bool $emptyAllowed = false): bool
    {
        if ($emptyAllowed && trim($submittedData[$key]) == '') {
            return true;
        }
        $result = true;
        $userUids = GeneralUtility::intExplode(',', $submittedData[$key]);
        foreach ($userUids as $uid) {
            if (!$this->validateUser($uid)) {
                $this->addMessage(str_replace('%1', $uid, $this->getLanguageService()
                    ->sL(PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalidUser.' . $key)), ContextualFeedbackSeverity::ERROR);
                $result = false;
            }
        }

        return $result;
    }

    private function validatePageAdditionalField(array &$submittedData, string $key): bool
    {
        $result = true;
        if (!$this->validatePage($submittedData[$key])) {
            $this->addMessage(str_replace('%1', $submittedData[$key], $this->getLanguageService()
                ->sL(PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalidPage.' . $key)), ContextualFeedbackSeverity::ERROR);
            $result = false;
        }

        return $result;
    }

    private function validatePage($pid): bool
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        return trim($pid) == strval(intval($pid)) && $pageRepository->getPage($pid, true) != null;
    }

    private function validateBooleanAdditionalField(array &$submittedData, string $key): bool
    {
        $result = true;
        if (!($submittedData[$key] === '0' || $submittedData[$key] === '1')) {
            $this->addMessage(str_replace('%1', $submittedData[$key], $this->getLanguageService()
                ->sL(PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalidBoolean.' . $key)), ContextualFeedbackSeverity::ERROR);
            $result = false;
        }

        return $result;
    }

    private function validateUrlAdditionalField(array &$submittedData, string $key): bool
    {
        $url = trim($submittedData[$key]);
        if (strlen($url) == 0) {
            return true;
        }
        if (!(is_string($url) && strlen($url) > 5 && filter_var($url, FILTER_VALIDATE_URL))) {
            $this->addMessage(str_replace('%1', $submittedData[$key], $this->getLanguageService()
                ->sL(PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.invalidUrl.' . $key)), ContextualFeedbackSeverity::ERROR);
            return false;
        }
        return true;
    }


    private function validateSitedField(array &$submittedData, string $key): bool
    {
        $result = true;

        try {
            GeneralUtility::makeInstance(SiteFinder::class)->getSiteByIdentifier($submittedData[$key]);
        } catch (\Exception $e) {
            $this->addMessage($this->getLanguageService()
                ->sL(PersonalDutyRosterPlanningAdditionalFieldProvider::TRANSLATION_PREFIX . 'error.siteNotFound.' . $key), ContextualFeedbackSeverity::ERROR);
            $result = false;
        }
        return $result;
    }

    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule): bool
    {
        $result = true;
        $result &= $this->validatePageAdditionalField($submittedData, PersonalDutyRosterPlanningTask::PLANNING_STORAGE_UID);
        $result &= $this->validatePagesAdditionalField($submittedData, PersonalDutyRosterPlanningTask::DUTY_ROSTER_STORAGE_UIDS);
        $result &= $this->validatePagesAdditionalField($submittedData, PersonalDutyRosterPlanningTask::FE_USER_STORAGE_UIDS);
        $result &= $this->validatePagesAdditionalField($submittedData, PersonalDutyRosterPlanningTask::FE_USERGROUP_STORAGE_UIDS);
        $result &= $this->validateUsersAdditionalField($submittedData, PersonalDutyRosterPlanningTask::SPECIFIED_USER_UIDS, true);
        $result &= $this->validateBooleanAdditionalField($submittedData, PersonalDutyRosterPlanningTask::RESET_USERS);
        $result &= $this->validateBooleanAdditionalField($submittedData, PersonalDutyRosterPlanningTask::ENABLE_REMINDER);
        $result &= $this->validatePageAdditionalField($submittedData, PersonalDutyRosterPlanningTask::PERSONAL_DUTY_ROSTER_PAGE_UID);
        $result &= $this->validateSitedField($submittedData, PersonalDutyRosterPlanningTask::SITE_IDENTIFIER);
        return $result;
    }

    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->set($submittedData);
    }

    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
