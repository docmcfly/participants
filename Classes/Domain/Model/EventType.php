<?php
namespace Cylancer\Participants\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 *
 * This file is part of the "Participants" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin 
 */
class EventType extends AbstractEntity
{

    /**
     * hidden target groups
     *
     * @var ObjectStorage<FrontendUserGroup>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $usergroups = null;

    /**
     *
     * @var string
     */
    protected $title = true;

    /**
     *
     * @var string
     */
    protected $description = true;

    /**
     * public
     *
     * @var bool
     */
    protected $public;

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
    }

    /**
     * Adds a user group
     *
     * @param FrontendUserGroup $usergroup
     * @return void
     */
    public function addUsergroup(FrontendUserGroup $usergroup)
    {
        $this->usergroups->attach($usergroup);
    }

    /**
     * Removes a user group
     *
     * @param FrontendUserGroup $usergroupToRemove
     *           
     * @return void
     */
    public function removeUsergroup(FrontendUserGroup $usergroupToRemove)
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
    public function setUsergroups(ObjectStorage $usergroups)
    {
        $this->usergroups = $usergroups;
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @param string $s
     */
    public function setTitle(string $s)
    {
        $this->title = $s;
    }

    /**
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     *
     * @param string $s
     */
    public function setDescription(string $s)
    {
        $this->description = $s;
    }

    /**
     * Returns the public
     *
     * @return int $public
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Sets the public
     *
     * @param int $public
     * @return void
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }
}