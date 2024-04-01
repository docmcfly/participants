<?php
namespace Cylancer\Participants\Domain\Model;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 C.Gogolin <service@cylancer.net>
 */
class CommitmentSettings
{

    /** @var boolean */
    protected $applyPlanningData;

    /** @var boolean */
    protected $infoMailWhenPersonalDutyRosterChanged = true;

    /** @var boolean */
    protected $personalDutyEventReminder = false;

    /**
     *
     * @return boolean
     */
    public function getApplyPlanningData()
    {
        return $this->applyPlanningData;
    }

    /**
     *
     * @param boolean $applyPlanningData
     */
    public function setApplyPlanningData($applyPlanningData)
    {
        $this->applyPlanningData = $applyPlanningData;
    }

    /**
     *
     * @return boolean
     */
    public function getInfoMailWhenPersonalDutyRosterChanged()
    {
        return $this->infoMailWhenPersonalDutyRosterChanged;
    }

    /**
     *
     * @param boolean $b
     */
    public function setInfoMailWhenPersonalDutyRosterChanged(bool $b)
    {
        $this->infoMailWhenPersonalDutyRosterChanged = $b;
    }

    /**
     *
     * @return boolean
     */
    public function getPersonalDutyEventReminder(): bool
    {
        return $this->personalDutyEventReminder;
    }

    /**
     *
     * @param boolean $b
     */
    public function setPersonalDutyEventReminder(bool $b): void
    {
        $this->personalDutyEventReminder = $b;
    }
}   