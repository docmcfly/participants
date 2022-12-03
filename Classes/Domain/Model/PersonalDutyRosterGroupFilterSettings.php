<?php
namespace Cylancer\Participants\Domain\Model;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Cylancer\Participants\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 */
class PersonalDutyRosterGroupFilterSettings
{
    
    public function __set(String $name ,  $value): void{
        if($name === 'settings'){
            $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            /** @var FrontendUserGroupRepository $frontendUserGroupRepository */
            $frontendUserGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class, $objectManager);
            $frontendUserGroupRepository->injectPersistenceManager($persistenceManager);
            foreach($value as  $k=>$v){
                $isVisible = is_array($v); 
                $this->add($frontendUserGroupRepository->findByUid(intval($k)) , $isVisible); 
            }
        }
    }
    
    
    private $settings = [];

    /**
     *
     * @param FrontendUserGroup $frontendUserGroup
     * @param bool $isVisible
     */
    public function add(FrontendUserGroup $frontendUserGroup, bool $isVisible): void
    {
        $this->settings[$frontendUserGroup->getUid()] = new PersonalDutyRosterGroupFilterSet($frontendUserGroup, $isVisible);
    }

    /**
     *
     * @param int $uid
     * @return PersonalDutyRosterGroupFilterSet
     */
    public function get(int $uid): PersonalDutyRosterGroupFilterSet
    {
        return array_key_exists($uid, $this->settings) ? $this->settings[$uid] : new PersonalDutyRosterGroupFilterSet(null, null);
    }

    /**
     *
     * @return PersonalDutyRosterGroupFilterSet[]
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     *
     * @param int $uid
     * @return bool
     */
    public function exists(int $uid): bool
    {
        return array_key_exists($uid, $this->settings);
    }

    /**
     *
     * @return bool
     */
    public function usable(): bool
    {
        return ! empty($this->settings);
    }
}
