<?php
namespace Cylancer\Participants\Service;

use Cylancer\Participants\Domain\Model\FrontendUser;
use Cylancer\Participants\Domain\Model\FrontendUserGroup;
use Cylancer\Participants\Domain\Model\PersonalDutyRosterGroupFilterSettings;
use Cylancer\Participants\Domain\Repository\FrontendUserGroupRepository;
use Cylancer\Participants\Domain\Repository\FrontendUserRepository;
use Cylancer\Participants\Controller\PersonalDutyRosterController;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

class PersonalDutyRosterService implements SingletonInterface
{


    public function __construct(
        private readonly FrontendUserService $frontendUserService,
        private readonly FrontendUserRepository $frontendUserRepository,
        private readonly FrontendUserGroupRepository $frontendUserGroupRepository,
    ) {

    }


    public function getPersonalDutyRosterFilterSettings(array $flexSettings, bool $ignoreCurrentUserSettings = false): array
    {
        $personalDutyRosterGroups = GeneralUtility::intExplode(',', $flexSettings[PersonalDutyRosterController::PERSONAL_DUTY_ROSTER_GROUPS], true);
        $optionalHiddenPersonalDutyRosterGroups = GeneralUtility::intExplode(',', $flexSettings[PersonalDutyRosterController::OPTIONAL_HIDDEN_PERSONAL_DUTY_ROSTER_GROUPS], true);

        /**
         *
         * @var FrontendUserGroup $hug
         * @var FrontendUser $u
         */
        $u = $this->frontendUserService->getCurrentUser();

        $userHiddenDutyRosterGroups = [];
        foreach ($u->getHiddenPersonalDutyRosterGroups() as $hug) {
            if (in_array($hug->getUid(), $personalDutyRosterGroups)) {
                $userHiddenDutyRosterGroups[$hug->getUid()] = $hug;
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
                    $hug = $this->frontendUserGroupRepository->findByUid($optionalGroups);
                    $personalDutyRosterFilterSettings->add($hug, !array_key_exists($hug->getUid(), $userHiddenDutyRosterGroups));
                }
            }
        }
        // debug($u);
        return [
            $personalDutyRosterGroups,
            $personalDutyRosterFilterSettings
        ];
    }

}