<?php
namespace Cylancer\Participants\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This file is part of the "User Tools" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Clemens Gogolin <service@cylancer.net>
 */
class TimeOut extends AbstractEntity
{

    /**  @var String */
    protected $from = null;

    /** @var String */
    protected $until = null;

    /**  @var FrontendUser */
    protected $user = null;

    /** @var String  */
    protected $reason = '';

    /**
     *
     * @return String from
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Sets the from date
     *
     * @param
     *            String from
     * @return void
     */
    public function setFrom(String $from)
    {
        $this->from = $from;
    }

    /**
     * Returns the until date
     *
     * @return String until
     */
    public function getUntil()
    {
        return $this->until;
    }

    /**
     * Sets the until date
     *
     * @param
     *            String until
     * @return void
     */
    public function setUntil(String $until)
    {
        $this->until = $until;
    }

    /**
     * Returns the user
     *
     * @return FrontendUser $user
     */
    public function getUser(): ?FrontendUser
    {
        return $this->user;
    }

    /**
     * Sets the user
     *
     * @param FrontendUser $user
     * @return void
     */
    public function setUser(FrontendUser $user): void
    {
        $this->user = $user;
    }

    /**
     * Returns the time out reason
     *
     * @return String reason
     */
    public function getReason(): String
    {
        return $this->reason;
    }

    /**
     * Sets the time out reason
     *
     * @param
     *            String reason
     * @return void
     */
    public function setReason(String $reason): void
    {
        $this->reason = $reason;
    }
}
