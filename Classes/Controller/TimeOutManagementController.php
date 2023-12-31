<?php
namespace Cylancer\Participants\Controller;

use Cylancer\Participants\Domain\Model\AddTimeOut;
use Cylancer\Participants\Domain\Model\TimeOut;
use Cylancer\Participants\Domain\Repository\TimeOutRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Cylancer\Participants\Domain\Repository\CommitmentRepository;
use Cylancer\Participants\Service\FrontendUserService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Cylancer\Participants\Domain\Model\ValidationResults;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 *
 * @package Cylancer\Participants\Controller
 */
class TimeOutManagementController extends ActionController
{

    const GERMAN_DATE_FORMAT = 'd.m.Y';

    const STANDARD_DATE_FORMAT = 'Y-m-d';

    const REASONS = 'reasons';

    const ADD_TIME_OUT = 'addTimeOut';

    const VALIDATIOPN_RESULTS = 'validationResults';

    /** @var FrontendUserService */
    private $frontendUserService = null;

    /** @var TimeOutRepository */
    public $timeOutRepository = null;

    /** @var CommitmentRepository   */
    public $commitmentRepository = null;

    /** @var PersistenceManager */
    public $persistenceManager = null;

    public function __construct(FrontendUserService $frontendUserService, TimeOutRepository $timeOutRepository, CommitmentRepository $commitmentRepository, PersistenceManager $persistenceManager)
    {
        $this->frontendUserService = $frontendUserService;
        $this->timeOutRepository = $timeOutRepository;
        $this->commitmentRepository = $commitmentRepository;
        $this->persistenceManager = $persistenceManager;
    }

    private $_validationResults = null;

    /**
     * action set defaults
     *
     * @return void
     */
    public function listAction(): void
    {
        /** @var ValidationResults $validationResults **/
        $validationResults = $this->getValidationResults();

        if ($this->frontendUserService->isLogged()) {

            $timeouts = $this->timeOutRepository->findByUser($this->frontendUserService->getCurrentUserUid());
            $this->view->assign('timeouts', $timeouts);

            // settings: reasons
            $tmp = explode("\n", str_replace("\r", "\n", str_replace("\r\n", "\n", $this->settings[TimeOutManagementController::REASONS])));
            $reasons = array();
            foreach ($tmp as &$value) {
                if (!empty($value)) {
                    $reasons[$value] = $value;
                }
            }

            $addTimeOut = $this->request->hasArgument(TimeOutManagementController::ADD_TIME_OUT) ? $this->request->getArgument(TimeOutManagementController::ADD_TIME_OUT) : new AddTimeOut();
            $addTimeOut->setReason(empty($tmp) ? '' : $tmp[0]);
            $this->view->assign('addTimeOut', $addTimeOut);
            $this->view->assign('reasons', $reasons);
        } else {
            $validationResults->addError('notLoggedIn');
        }
        $this->view->assign(TimeOutManagementController::VALIDATIOPN_RESULTS, $validationResults);
    }

    /**
     * deletes a time out
     *
     * @param TimeOut $timeout
     * @return void
     */
    public function deleteAction(TimeOut $timeout = null): void
    {
        if ($this->frontendUserService->isLogged() && $timeout->getUser() != null && $timeout->getUser()->getUid() === $this->frontendUserService->getCurrentUserUid()) {
            $this->timeOutRepository->remove($timeout);
        }
        $this->redirect('list');
    }

    /**
     * create a time out
     *
     * @param  AddTimeOut addTimeOut
     * @return object
     */
    public function createAction(AddTimeOut $addTimeOut = null): object
    {
        /** @var ValidationResults $validationResults **/
        $validationResults = $this->validate($addTimeOut);
        if (!$validationResults->hasErrors()) {
            $timeOut = new TimeOut();
            $timeOut->setFrom($this->transformDate($addTimeOut->getFrom()));
            $timeOut->setUntil($this->transformDate($addTimeOut->getUntil()));
            $timeOut->setReason($addTimeOut->getReason());
            $timeOut->setUser($this->frontendUserService->getCurrentUser());
            $this->timeOutRepository->add($timeOut);
            if ($addTimeOut->getUpdateCommitments()) {
                $f = \DateTime::createFromFormat('!' . TimeOutManagementController::STANDARD_DATE_FORMAT, $timeOut->getFrom());
                $n = new \DateTime();
                $t = \DateTime::createFromFormat('!' . TimeOutManagementController::STANDARD_DATE_FORMAT, $n->format(TimeOutManagementController::STANDARD_DATE_FORMAT));
                $u = \DateTime::createFromFormat('!' . TimeOutManagementController::STANDARD_DATE_FORMAT, $timeOut->getUntil());
                $this->commitmentRepository->dropout($this->frontendUserService->getCurrentUserUid(), $t->getTimestamp() > $f->getTimestamp() ? $t : $f, $u);
            }
            $this->persistenceManager->persistAll();
            $addTimeOut = new AddTimeOut();
            $validationResults->addInfo('savedSuccessful');
        }

        return GeneralUtility::makeInstance(ForwardResponse::class, 'list')->withArguments([
            TimeOutManagementController::ADD_TIME_OUT => $addTimeOut,
            TimeOutManagementController::VALIDATIOPN_RESULTS => $validationResults
        ]);
    }

    /**
     * transform from dd.mm.yyyy to yyyy-mm-dd
     *
     * @param string germanDate
     * @return string
     */
    private function transformDate(string $germanDate): string
    {
        return \DateTime::createFromFormat('!' . TimeOutManagementController::GERMAN_DATE_FORMAT, $germanDate)->format(TimeOutManagementController::STANDARD_DATE_FORMAT);
    }

    /**
     *
     * @param
     *            ddTimeOut addTimeOut
     * @return ValidationResults
     */
    private function validate(AddTimeOut $addTimeOut = null): ValidationResults
    {
        /** @var ValidationResults $validationResults **/
        $validationResults = $this->getValidationResults();

        if (!$this->frontendUserService->isLogged()) {
            $validationResults->addError('notLoggedIn');

        } else {
            if ($addTimeOut == null) {
                $validationResults->addError('mysteryError');
            } else {
                if (empty(trim($addTimeOut->getFrom()))) {
                    $validationResults->addError('invalidFrom');
                } else {
                    $from = \DateTime::createFromFormat('!' . TimeOutManagementController::GERMAN_DATE_FORMAT, $addTimeOut->getFrom());
                    if ($from === false) {
                        $validationResults->addError('invalidFrom');
                    }
                }
                if (empty(trim($addTimeOut->getUntil()))) {
                    $validationResults->addError('invalidUntil');
                } else {
                    $until = \DateTime::createFromFormat('!' . TimeOutManagementController::GERMAN_DATE_FORMAT, $addTimeOut->getUntil());
                    if ($until === false) {
                        $validationResults->addError('invalidUntil');
                    }
                }
                if (!$validationResults->hasErrors() && $from->getTimestamp() > $until->getTimestamp()) {
                    $validationResults->addError('untilBeforeFrom');
                }
            }
        }
        return $validationResults;
    }

    private function getValidationResults()
    {
        if ($this->_validationResults == null) {
            $this->_validationResults = ($this->request->hasArgument(TimeOutManagementController::VALIDATIOPN_RESULTS)) ? //
                $this->request->getArgument(TimeOutManagementController::VALIDATIOPN_RESULTS) : //
                new ValidationResults();
        }
        return $this->_validationResults;
    }
}