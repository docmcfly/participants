<?php
namespace Cylancer\Participants\Controller;

use Cylancer\Participants\Domain\Model\CommitmentSettings;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Cylancer\Participants\Service\FrontendUserService;
use Cylancer\Participants\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use Cylancer\Participants\Domain\Repository\FrontendUserRepository;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 C. Gogolin <service@cylancer.net>
 *
 * @package Cylancer\Participants\Controller
 */
class CommitmentSettingsController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /** @var FrontendUserService */
    private $frontendUserService = null;

    /** @var PersistenceManager */
    private $persistenceManager;

    /** @var FrontendUserRepository  */
    private $frontendUserRepository = null;

    public function __construct(FrontendUserService $frontendUserService, PersistenceManager $persistenceManager, FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserService = $frontendUserService;
        $this->persistenceManager = $persistenceManager;
        $this->frontendUserRepository = $frontendUserRepository;
    }

    /**
     *
     * @return void
     */
    public function showAction(): void
    {
        /** @var FrontendUser $u */
        $u = $this->frontendUserRepository->findByUid($this->frontendUserService->getCurrentUserUid());
        if ($u != null) {
            $s = new CommitmentSettings();
            $s->setApplyPlanningData($u->getApplyPlanningData());
            $s->setInfoMailWhenPersonalDutyRosterChanged($u->getInfoMailWhenPersonalDutyRosterChanged());
            $s->setPersonalDutyEventReminder($u->getPersonalDutyEventReminder());
            $this->view->assign('commitmentSettings', $s);
        }
    }

    /**
     * create a time out
     *
     * @param
     *            CommitmentSettings commitmentSettings
     * @return void
     */
    public function saveAction(CommitmentSettings $commitmentSettings)
    {
        /**
         *
         * @var FrontendUser $u
         */
        $u = $this->frontendUserRepository->findByUid($this->frontendUserService->getCurrentUserUid());
        if ($u != null) {
            $u->setApplyPlanningData($commitmentSettings->getApplyPlanningData());
            $u->setInfoMailWhenPersonalDutyRosterChanged($commitmentSettings->getInfoMailWhenPersonalDutyRosterChanged());
            $u->setPersonalDutyEventReminder($commitmentSettings->getPersonalDutyEventReminder());
            $this->frontendUserRepository->update($u);
            $this->persistenceManager->persistAll();
        }

        return GeneralUtility::makeInstance(ForwardResponse::class, 'show');
    }
}
