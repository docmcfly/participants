<?php
namespace Cylancer\Participants\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This file is part of the "Participants" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 */
class Event extends AbstractEntity
{

    /**
     *
     * @var ObjectStorage<FrontendUserGroup>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $usergroups = null;

    /**
     *
     * @var ObjectStorage<FrontendUserGroup>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $publicUsergroups = null;

    /**
     *
     * @var EventType
     */
    protected $eventType = null;

    /**
     *
     * @var string
     */
    protected $description = true;

    /**
     *
     * @var bool
     */
    protected $showPublicDescription = false;

    /**
     *
     * @var string
     */
    protected $publicDescription = true;

    /**
     *
     * @var bool
     */
    protected $showPublicUsergroups = false;

    /**
     *
     * @var bool
     */
    protected $canceled = false;

    /**
     *
     * @var bool
     */
    protected $fullDay = false;

    /**
     *
     * @var string
     */
    protected $date = '0000-00-00';

    /**
     *
     * @var string
     */
    protected $time = '00:00';

    /**
     *
     * @var int
     */
    protected $current = - 1;

    /**
     *
     * @var int
     */
    protected $duration = 3;

    /**
     *
     * @var array
     */
    protected $visiblePublicUsergroups = null;

    /**
     *
     * @var array
     */
    protected $visibleUsergroups = null;

    /**
     *
     * @var int
     */
    protected $public;

    /**
     *
     * @var \DateTime
     */
    protected $crdate;

    /**
     *
     * @var \DateTime
     */
    protected $tstamp;

    /**
     *
     * @var bool
     */
    protected $hidden;

    /**
     *
     * @var bool
     */
    protected $deleted;

    /**
     * __construct
     */
    public function __construct()
    {

        // Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->usergroups = new ObjectStorage();
        $this->publicUsergroups = new ObjectStorage();
    }

    /**
     * Adds a user group
     *
     * @param FrontendUserGroup $usergroup
     * @return void
     */
    public function addUsergroup(FrontendUserGroup $usergroup): void
    {
        $this->usergroups->attach($usergroup);
    }

    /**
     * Removes a user group
     *
     * @param FrontendUserGroup $usergroupToRemove
     *            The Category to be removed
     * @return void
     */
    public function removeUsergroup(FrontendUserGroup $usergroupToRemove): void
    {
        $this->usergroups->detach($usergroupToRemove);
    }

    /**
     * Returns the usergroups
     *
     * @return ObjectStorage<FrontendUserGroup> $usergroups
     */
    public function getUsergroups()
    {
        return $this->usergroups;
    }

    /**
     * Sets the usergroups
     *
     * @param ObjectStorage<FrontendUserGroup> $usergroups
     * @return void
     */
    public function setUsergroups(ObjectStorage $usergroups): void
    {
        $this->usergroups = $usergroups;
    }

    /**
     * Adds a user group
     *
     * @param FrontendUserGroup $publicUsergroup
     * @return void
     */
    public function addPublicUsergroup(FrontendUserGroup $publicUsergroup): void
    {
        $this->publicUsergroups->attach($publicUsergroup);
    }

    /**
     * Removes a user group
     *
     * @param FrontendUserGroup $publicUsergroupToRemove
     *            The Category to be removed
     * @return void
     */
    public function removePublicUsergroup(FrontendUserGroup $publicUsergroupToRemove): void
    {
        $this->publicUsergroups->detach($publicUsergroupToRemove);
    }

    /**
     * Returns the publicUsergroups
     *
     * @return ObjectStorage<FrontendUserGroup> $publicUsergroups
     */
    public function getPublicUsergroups()
    {
        return $this->publicUsergroups;
    }

    /**
     * Sets the publicUsergroups
     *
     * @param ObjectStorage<FrontendUserGroup> $publicUsergroups
     * @return void
     */
    public function setPublicUsergroups(ObjectStorage $publicUsergroups): void
    {
        $this->publicUsergroups = $publicUsergroups;
    }

    /**
     *
     * @return EventType
     */
    public function getEventType(): EventType
    {
        return $this->eventType;
    }

    /**
     *
     * @param EventType $eventType
     * @return void
     */
    public function setEventType(EventType $eventType): void
    {
        $this->eventType = $eventType;
    }

    /**
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     *
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     *
     * @return boolean
     */
    public function getShowPublicUsergroups(): bool
    {
        return $this->showPublicUsergroups;
    }

    /**
     *
     * @param boolean $showPublicUsergroups
     * @return void
     */
    public function setShowPublicUsergroups($showPublicUsergroups): void
    {
        $this->showPublicUsergroups = $showPublicUsergroups;
    }

    /**
     *
     * @return boolean
     */
    public function getFullDay(): bool
    {
        return $this->fullDay;
    }

    /**
     *
     * @param boolean $fullDay
     * @return void
     */
    public function setFullDay($fullDay): void
    {
        $this->fullDay = $fullDay;
    }

    /**
     *
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return \DateTime::createFromFormat('Y-m-d', $this->date);
    }

    /**
     *
     * @param \DateTime $date
     * @return void
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date->format('Y-m-d');
    }

    /**
     *
     * @return \DateTime
     */
    public function getTime(): \DateTime
    {
        return \DateTime::createFromFormat('H:i:s', $this->time);
    }

    /**
     *
     * @param \DateTime $time
     * @return void
     */
    public function setTime(\DateTime $time): void
    {
        $this->time = $time->format('H:i:s');
    }

    /**
     *
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     *
     * @param int $duration
     * @return void
     */
    public function setDuration($duration): void
    {
        $this->duration = $duration;
    }

    /**
     *
     * @return \DateTime
     */
    public function getDateTime(): \DateTime
    {
        return $this->getFullDay() ? \DateTime::createFromFormat('Y-m-d', $this->date)->setTime(0, 0) : \DateTime::createFromFormat('Y-m-d H:i:s', $this->date . ' ' . $this->time);
    }

    /**
     *
     * @return void
     */
    private function computePublicVisibleUsergroups(): void
    {
        /** @var FrontendUserGroup $ug       */
        $tmp = array();
        if ($this->showPublicUsergroups) {
            foreach ($this->getPublicUsergroups() as $ug) {
                $tmp[$ug->getUid()] = $ug;
            }
            $tmp = array_values($tmp);
            usort($tmp, function (FrontendUserGroup $a, FrontendUserGroup $b): int {
                $x = strnatcmp($a->getTitle(), $b->getTitle());
                if ($x == 0) {
                    return strnatcmp($a->getAccronym(), $b->getAccronym());
                }
                return $x;
            });
            $this->visiblePublicUsergroups = $tmp;
        } else {
            $this->computeVisibleUsergroups();
            $this->visiblePublicUsergroups = $this->visibleUsergroups;
        }
    }

    /**
     *
     * @return array
     */
    public function getVisiblePublicUsergroups(): array
    {
        if ($this->visiblePublicUsergroups == null) {
            $this->computePublicVisibleUsergroups();
        }
        return $this->visiblePublicUsergroups;
    }

    /**
     *
     * @return void
     */
    private function computeVisibleUsergroups(): void
    {
        /**
         *
         * @var FrontendUserGroup $ug
         */
        $tmp = array();

        foreach ($this->getEventType()->getUsergroups() as $ug) {
            $tmp[$ug->getUid()] = $ug;
        }
        foreach ($this->getUsergroups() as $ug) {
            $tmp[$ug->getUid()] = $ug;
        }

        $tmp = array_values($tmp);
        usort($tmp, function (FrontendUserGroup $a, FrontendUserGroup $b): int {
            $x = strnatcmp($a->getTitle(), $b->getTitle());
            if ($x == 0) {
                return strnatcmp($a->getAccronym(), $b->getAccronym());
            }
            return $x;
        });

        $this->visibleUsergroups = $tmp;
    }

    /**
     *
     * @return array
     */
    public function getVisibleUsergroups(): array
    {
        if ($this->visibleUsergroups == null) {
            $this->computeVisibleUsergroups();
        }
        return $this->visibleUsergroups;
    }

    /**
     *
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->current;
    }

    /**
     *
     * @param int $current
     */
    public function setCurrent(int $current)
    {
        $this->current = $current;
    }

    /**
     * Get creation date
     *
     * @return int
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Set creation date
     *
     * @param int $crdate
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }

    public function getTstamp()
    {
        return $this->tstamp;
    }

    public function setTstamp($tstamp): void
    {
        $this->tstamp = $tstamp;
    }

    /**
     * Get hidden flag
     *
     * @return int
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set hidden flag
     *
     * @param int $hidden
     *            hidden flag
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * Get deleted flag
     *
     * @return int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set deleted flag
     *
     * @param int $deleted
     *            deleted flag
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Set public option
     *
     * @param int $public
     */
    public function setPublic(int $public): void
    {
        $this->public = $public;
    }

    /**
     * Get public flag
     *
     * @return int
     */
    public function getPublic(): int
    {
        return $this->public;
    }

    /**
     *
     * @return boolean
     */
    public function getShowPublicDescription(): bool
    {
        return $this->showPublicDescription;
    }

    /**
     *
     * @param boolean $showPublicDescription
     * @return void
     */
    public function setShowPublicDescription(bool $showPublicDescription): void
    {
        $this->showPublicDescription = $showPublicDescription;
    }

    /**
     *
     * @return string
     */
    public function getPublicDescription(): string
    {
        return $this->publicDescription;
    }

    /**
     *
     * @param string $publicDescription
     * @return void
     */
    public function setPublicDescription(bool $publicDescription): void
    {
        $this->publicDescription = $publicDescription;
    }

    /**
     *
     * @return boolean
     */
    public function getCanceled(): bool
    {
        return $this->canceled;
    }

    /**
     *
     * @param boolean $canceled
     * @return void
     */
    public function setCanceled(bool $canceled): void
    {
        $this->canceled = $canceled;
    }
}