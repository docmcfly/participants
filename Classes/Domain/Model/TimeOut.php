<?php
namespace Cylancer\Participants\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This file is part of the "User Tools" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 C.Gogolin <service@cylancer.net>
 */
class TimeOut extends AbstractEntity
{

    /**  @var string */
    protected $from = null;

    /** @var string */
    protected $until = null;

    /**  @var FrontendUser */
    protected $user = null;

    /** @var string  */
    protected $reason = '';

    /**
     *
     * @return string from
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Sets the from date
     *
     * @param            string from
     * @return void
     */
    public function setFrom(string $from)
    {
        $this->from = $from;
    }

    /**
     * Returns the until date
     *
     * @return string until
     */
    public function getUntil()
    {
        return $this->until;
    }

    /**
     * Sets the until date
     *
     * @param
     *            string until
     * @return void
     */
    public function setUntil(string $until)
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
     * @return string reason
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * Sets the time out reason
     *
     * @param  string reason
     * @return void
     */
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }
}